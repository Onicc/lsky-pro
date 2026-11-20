import './bootstrap';
import '../css/app.css';
import '../css/common.less';
import '../css/gallery.less';
import '../css/context-js.less';
import '@fortawesome/fontawesome-free/css/fontawesome.min.css';
import '@fortawesome/fontawesome-free/css/solid.min.css';
import '@fortawesome/fontawesome-free/css/brands.min.css';
import 'toastr/build/toastr.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';

import Alpine from 'alpinejs';
import Sidebar from './stores/sidebar';
import Modal from './stores/modal';
import { mountUploader } from './uploader';

Alpine.store('sidebar', Sidebar);
Alpine.store('modal', Modal);

window.Alpine = Alpine;

Alpine.start();

window.utils = {
    formatSize(bytes, decimal) {
        if (bytes === 0) return '0 B';

        let c = 1024,
            d = decimal || 2,
            e = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
            f = Math.floor(Math.log(bytes) / Math.log(c));

        return parseFloat((bytes / Math.pow(c, f)).toFixed(d)) + " " + e[f];
    },
    /**
     * 更新进度条
     * @param size 增加的字节(kb)
     * @param total 总共有多少字节(kb)
     */
    setCapacityProgress(size, total) {
        let $progress = $('#capacity-progress');
        if ($progress.length) {
            let used = parseFloat($progress.find('progress').val()) + size;
            $progress.find('progress').val(used);
            $progress.find('span.used').text(utils.formatSize(used * 1024));

            if (total !== undefined && typeof total === 'number') {
                total = parseFloat($progress.find('progress').attr('max')) + total;
                $progress.find('progress').attr('max', total);
                $progress.find('span.total').text(utils.formatSize(total * 1024));
            }
        }
    },
    guid() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            let r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },
    isMobile() {
        if (navigator.userAgent.match(/Android/i)
            || navigator.userAgent.match(/webOS/i)
            || navigator.userAgent.match(/iPhone/i)
            || navigator.userAgent.match(/iPad/i)
            || navigator.userAgent.match(/iPod/i)
            || navigator.userAgent.match(/BlackBerry/i)
            || navigator.userAgent.match(/Windows Phone/i)
        ) {
            return window.screen.width < 768;
        }
        return false;
    },
    template(source, values) {
        const entities = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'};
        return source.replace(/__([a-z0-9_]+)__/g, (token, key) => {
            if (!Object.hasOwn(values, key)) return token;
            return String(values[key] ?? '').replace(/[&<>"']/g, letter => entities[letter]);
        });
    },
    infiniteScroll(selector, options) {
        const viewport = document.querySelector(selector);
        if (!viewport) return;

        const footer = document.createElement('div');
        footer.className = `infinite-scroll ${(options.classes || []).join(' ')}`;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'rounded-md px-4 py-2 text-sm text-gray-700 disabled:cursor-default';
        footer.append(button);
        viewport.append(footer);

        let params = {page: 1};
        let pending;
        const state = {loading: false, finished: false};

        async function load() {
            if (state.loading || state.finished) return;
            if (typeof options.data === 'function') params = options.data(params) || {};
            const request = new AbortController();
            pending = request;
            state.loading = true;
            button.disabled = true;
            button.textContent = options.loadingText || '正在加载…';
            try {
                const {data} = await axios.get(options.url, {params: {...params}, signal: request.signal});
                if (pending !== request) return;
                if (!data.status) throw new Error(data.message || '加载失败');
                options.success?.call(state, data);
                button.textContent = state.finished
                    ? (typeof options.finishedText === 'function' ? options.finishedText(params) : options.finishedText || '已显示全部')
                    : '加载更多';
                if (params.page !== undefined) params.page += 1;
            } catch (error) {
                if (pending !== request) return;
                button.textContent = options.errorText || '加载失败，点击重试';
                options.error?.call(state, error);
            } finally {
                if (pending !== request) return;
                state.loading = false;
                button.disabled = state.finished;
                options.complete?.call(state);
            }
        }

        const onScroll = () => {
            if (viewport.scrollTop + viewport.clientHeight >= viewport.scrollHeight - (options.offset || 30)) load();
        };
        button.addEventListener('click', load);
        viewport.addEventListener('scroll', onScroll, {passive: true});
        load();

        return {
            refresh(next) {
                pending?.abort();
                params = {...params, ...next};
                state.loading = false;
                state.finished = false;
                load();
            },
            reset() {
                params = {page: 1};
                this.refresh({});
            },
            destroy() {
                pending?.abort();
                pending = null;
                viewport.removeEventListener('scroll', onScroll);
                footer.remove();
            },
        };
    }
}

for (const root of document.querySelectorAll('[data-uploader]')) mountUploader(root);
