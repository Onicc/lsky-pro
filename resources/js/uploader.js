const messages = {
    queued: '等待上传',
    uploading: '正在上传',
    processing: '服务器正在处理',
    success: '上传成功',
    failed: '上传失败',
    cancelled: '已取消',
};

function newRequestId() {
    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 15) | 64;
    bytes[8] = (bytes[8] & 63) | 128;
    const hex = Array.from(bytes, value => value.toString(16).padStart(2, '0')).join('');
    return [hex.slice(0, 8), hex.slice(8, 12), hex.slice(12, 16), hex.slice(16, 20), hex.slice(20)].join('-');
}

function uploadError(response, payload) {
    if (typeof payload?.message === 'string' && payload.message) return payload.message;
    if (response === 413) return '超过服务器允许的上传大小';
    if (response === 419) return '登录状态已过期，请刷新页面后重试';
    if (response === 429) return '上传过于频繁，请稍后重试';
    if (response === 401 || response === 403) return '当前账户没有上传权限';
    return '服务器暂时无法完成上传';
}

async function copyText(text) {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch {
            // Some browser configurations expose the Clipboard API but deny writes.
        }
    }
    const selected = document.activeElement;
    const field = document.createElement('textarea');
    field.value = text;
    field.style.position = 'fixed';
    field.style.left = '-9999px';
    document.body.append(field);
    field.select();
    try {
        return document.execCommand('copy');
    } finally {
        field.remove();
        selected?.focus();
    }
}

export function mountUploader(root) {
    const find = name => root.querySelector(`[data-upload-${name}]`);
    const picker = find('picker');
    if (!picker) return;

    const batch = find('batch');
    const list = find('list');
    const status = find('summary');
    const notice = find('alert');
    const copyStatus = find('copy-status');
    const format = find('format');
    const start = find('start');
    const clear = find('clear');
    const copy = find('copy');
    const drop = find('drop');
    const items = [];
    const extensions = root.dataset.extensions.split(',');
    const maxBytes = Number(root.dataset.maxBytes);
    const concurrency = Math.max(1, Math.min(10, Number(root.dataset.concurrency) || 3));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let active = 0;

    function refresh() {
        const complete = items.filter(item => item.state === 'success').length;
        const failures = items.filter(item => item.state === 'failed').length;
        batch.hidden = items.length === 0;
        status.textContent = `${items.length} 张，完成 ${complete} 张` + (failures ? `，失败 ${failures} 张` : '');
        start.disabled = !items.some(item => item.state === 'queued' && !item.scheduled);
        clear.disabled = complete === 0;
        copy.disabled = !items.some(item => item.links?.[format.value]);
    }

    function showState(item) {
        const working = item.state === 'uploading' || item.state === 'processing';
        const retry = item.state === 'failed' || item.state === 'cancelled';
        item.parts.state.textContent = item.error ? `${messages[item.state]} · ${item.error}` : messages[item.state];
        item.parts.progress.hidden = !working;
        const value = item.links?.[format.value] || '';
        item.parts.link.hidden = !value;
        item.parts.link.value = value;
        item.parts.copy.hidden = !value;
        item.parts.action.textContent = working ? '取消' : retry ? '重试' : '移除';
        if (item.state === 'success' && root.dataset.hideFinishedPreviews === '1') releasePreview(item);
    }

    function releasePreview(item) {
        if (!item.objectUrl) return;
        URL.revokeObjectURL(item.objectUrl);
        item.objectUrl = null;
        item.parts.preview.removeAttribute('src');
        item.parts.preview.hidden = true;
    }

    function remove(item) {
        items.splice(items.indexOf(item), 1);
        releasePreview(item);
        item.row.remove();
        refresh();
    }

    function add(files, immediate = false) {
        const rejected = [];
        for (const file of files) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!extensions.includes(ext) || file.size > maxBytes) {
                rejected.push(file.name);
                continue;
            }
            const row = find('row').content.firstElementChild.cloneNode(true);
            const parts = Object.fromEntries(['preview', 'name', 'state', 'progress', 'link', 'copy', 'action']
                .map(name => [name, row.querySelector(`[data-${name}]`)]));
            const item = { file, row, parts, id: newRequestId(), state: 'queued', scheduled: immediate };
            parts.name.textContent = file.name;
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'avif'].includes(ext)) {
                item.objectUrl = URL.createObjectURL(file);
                parts.preview.src = item.objectUrl;
                parts.preview.hidden = false;
                parts.preview.addEventListener('error', () => releasePreview(item), { once: true });
            }
            parts.copy.addEventListener('click', () => copyLinks([item]));
            parts.action.addEventListener('click', () => {
                if (item.xhr) {
                    item.xhr.abort();
                } else if (item.state === 'failed' || item.state === 'cancelled') {
                    item.state = 'queued';
                    item.scheduled = true;
                    item.error = '';
                    showState(item);
                    drain();
                } else {
                    remove(item);
                }
            });
            items.push(item);
            list.append(row);
            showState(item);
        }
        notice.textContent = rejected.length ? `格式或大小超出限制：${rejected.join('、')}` : '';
        refresh();
        if (immediate) drain();
    }

    function drain() {
        while (active < concurrency) {
            const next = items.find(item => item.scheduled && item.state === 'queued');
            if (!next) break;
            send(next);
        }
        refresh();
    }

    function send(item) {
        const xhr = new XMLHttpRequest();
        const payload = new FormData();
        if (!item.destination) {
            item.destination = Object.fromEntries([
                ['strategy_id', find('strategy')?.value],
                ['album_id', find('album')?.value],
                ['permission', find('permission')?.value],
            ].filter(([, value]) => value !== undefined));
        }
        for (const [field, value] of Object.entries(item.destination)) payload.append(field, value);
        payload.append('file', item.file);
        active += 1;
        item.state = 'uploading';
        item.xhr = xhr;
        item.parts.progress.value = 0;
        showState(item);

        function finish(state, error = '') {
            active -= 1;
            item.state = state;
            item.error = error;
            item.xhr = null;
            showState(item);
            drain();
        }

        xhr.open('POST', root.dataset.endpoint);
        xhr.responseType = 'json';
        xhr.timeout = 180000;
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        xhr.setRequestHeader('Idempotency-Key', item.id);
        xhr.upload.onprogress = event => {
            if (!event.lengthComputable) return;
            item.parts.progress.value = Math.floor(event.loaded / event.total * 100);
            if (event.loaded === event.total) {
                item.state = 'processing';
                showState(item);
            }
        };
        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300 && xhr.response?.status && xhr.response?.data?.links) {
                item.links = xhr.response.data.links;
                window.utils.setCapacityProgress(Number(xhr.response.data.size) || 0);
                finish('success');
            } else {
                finish('failed', uploadError(xhr.status, xhr.response));
            }
        };
        xhr.onerror = () => finish('failed', '网络连接中断');
        xhr.ontimeout = () => finish('failed', '等待服务器响应超时');
        xhr.onabort = () => finish('cancelled');
        xhr.send(payload);
    }

    async function copyLinks(selected) {
        const links = selected.map(item => item.links?.[format.value]).filter(Boolean);
        if (links.length === 0) return;
        const ok = await copyText(links.join('\n')).catch(() => false);
        copyStatus.textContent = ok ? `已复制 ${links.length} 条链接` : '浏览器限制了复制，可从对应文件行手动选中链接';
    }

    picker.addEventListener('change', () => {
        add(Array.from(picker.files));
        picker.value = '';
    });
    drop.addEventListener('dragover', event => {
        event.preventDefault();
        drop.classList.add('border-indigo-500', 'bg-indigo-50');
    });
    drop.addEventListener('dragleave', event => {
        if (!drop.contains(event.relatedTarget)) drop.classList.remove('border-indigo-500', 'bg-indigo-50');
    });
    drop.addEventListener('drop', event => {
        event.preventDefault();
        drop.classList.remove('border-indigo-500', 'bg-indigo-50');
        add(Array.from(event.dataTransfer.files));
    });
    document.addEventListener('paste', event => {
        if (event.target.closest?.('input:not([type="file"]), textarea, [contenteditable="true"]')) return;
        const files = Array.from(event.clipboardData?.files || []);
        if (files.length === 0) return;
        event.preventDefault();
        add(files, root.dataset.pasteImmediate === '1');
    });
    start.addEventListener('click', () => {
        items.filter(item => item.state === 'queued').forEach(item => { item.scheduled = true; });
        drain();
    });
    clear.addEventListener('click', () => items.filter(item => item.state === 'success').forEach(remove));
    copy.addEventListener('click', () => copyLinks(items));
    format.addEventListener('change', () => {
        copyStatus.textContent = '';
        items.forEach(showState);
        refresh();
    });
    refresh();
}
