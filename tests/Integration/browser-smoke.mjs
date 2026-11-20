import assert from 'node:assert/strict';
import { deflateSync, crc32 } from 'node:zlib';
import { chromium } from 'playwright-core';

const base = process.env.LSKY_BROWSER_BASE_URL;
const endpoint = process.env.LSKY_BROWSER_CDP_ENDPOINT;
const email = process.env.LSKY_BROWSER_EMAIL;
const password = process.env.LSKY_BROWSER_PASSWORD;
assert(base && endpoint && email && password, 'Set test site URL, Chrome CDP endpoint and disposable account credentials.');

function png(name, rgb) {
    function chunk(type, data) {
        const header = Buffer.alloc(8);
        header.writeUInt32BE(data.length);
        header.write(type, 4);
        const checksum = Buffer.alloc(4);
        checksum.writeUInt32BE(crc32(Buffer.concat([header.subarray(4), data])));
        return Buffer.concat([header, data, checksum]);
    }
    const imageHeader = Buffer.from([0, 0, 0, 16, 0, 0, 0, 16, 8, 2, 0, 0, 0]);
    const scanline = Buffer.from([0, ...Array.from({ length: 16 }, () => rgb).flat()]);
    const image = Buffer.concat(Array.from({ length: 16 }, () => scanline));
    return {
        name,
        mimeType: 'image/png',
        buffer: Buffer.concat([
            Buffer.from('89504e470d0a1a0a', 'hex'),
            chunk('IHDR', imageHeader),
            chunk('IDAT', deflateSync(image)),
            chunk('IEND', Buffer.alloc(0)),
        ]),
    };
}

const browser = await chromium.connectOverCDP(endpoint);
try {
    for (const [name, viewport] of [['桌面', { width: 1440, height: 900 }], ['移动端', { width: 390, height: 844 }]]) {
        const context = await browser.newContext({ viewport, hasTouch: name === '移动端', isMobile: name === '移动端' });
        try {
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.route(url => url.origin !== new URL(base).origin, request => request.abort());
            const login = await page.goto(`${base}/login`);
            assert.equal(login.status(), 200);
            await page.locator('#email').fill(email);
            await page.locator('#password').fill(password);
            await Promise.all([page.waitForURL('**/dashboard'), page.locator('button[type="submit"]').click()]);
            const upload = await page.goto(`${base}/upload`);
            assert.equal(upload.status(), 200);
            await page.locator('[data-upload-picker]').setInputFiles([
                png('red.png', [219, 41, 51]),
                png('blue.png', [28, 61, 232]),
            ]);
            await page.getByRole('button', { name: '开始上传' }).click();
            const list = page.getByRole('list', { name: '上传列表' });
            try {
                await list.getByText('上传成功', { exact: true }).nth(1).waitFor({timeout: 15000});
            } catch (error) {
                const states = await list.locator('[data-state]').allTextContents();
                throw new Error(`Both pictures should upload successfully. Row states: ${states.join(' | ')}`, {cause: error});
            }
            assert.deepEqual(await list.locator('[data-name]').allTextContents(), ['red.png', 'blue.png']);
            const links = await list.locator('[data-link]').evaluateAll(fields => fields.map(field => field.value));
            assert.equal(new Set(links).size, 2);
            const pixels = await page.evaluate(async urls => Promise.all(urls.map(async url => {
                const response = await fetch(url);
                if (!response.ok) throw new Error(`Media read failed with ${response.status}`);
                const image = await createImageBitmap(await response.blob());
                const canvas = new OffscreenCanvas(1, 1);
                const ctx = canvas.getContext('2d');
                ctx.drawImage(image, 0, 0, 1, 1);
                return Array.from(ctx.getImageData(0, 0, 1, 1).data);
            })), links);
            assert(pixels[0][0] > pixels[0][2] * 3 && pixels[1][2] > pixels[1][0] * 3, JSON.stringify(pixels));
            assert(await page.getByRole('button', { name: '复制已完成链接' }).isEnabled());
            await page.getByLabel('链接格式').selectOption('markdown');
            assert.match(await list.locator('[data-link]').first().inputValue(), /^!\[red\.png\]\(/);

            await page.goto(`${base}/images`);
            await page.waitForFunction(() => {
                const previews = Array.from(document.querySelectorAll('#images-grid .images-item img')).slice(0, 2);
                return previews.length === 2 && previews.every(image => image.complete && image.naturalWidth > 0);
            });
            assert(await page.getByRole('textbox', { name: '搜索图片' }).isVisible());
            await page.getByRole('textbox', { name: '搜索图片' }).fill('blue.png');
            await page.getByRole('textbox', { name: '搜索图片' }).press('Enter');
            await page.locator('.images-item .filename').getByText('blue.png').first().waitFor();
            await page.locator('.images-item').first().focus();
            await page.keyboard.press('Space');
            await page.getByText('已选择 1 张图片', { exact: true }).waitFor();
            assert.deepEqual(errors, []);
            console.log(`${name}通过登录、批量上传、像素读回、Markdown 映射及键盘选图`);
        } finally {
            await context.close();
        }
    }
} finally {
    await browser.close();
}
