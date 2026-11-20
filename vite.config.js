import { createHash } from 'node:crypto';
import { readFile } from 'node:fs/promises';
import { defineConfig } from 'vite';

const pageAssets = {
    'clipboard/clipboard.js': 'node_modules/clipboard/dist/clipboard.min.js',
    'clipboard/image.js': 'node_modules/copy-image-clipboard/dist/index.browser.js',
    'context-menu.js': 'resources/js/context-js.js',
    'dragselect.js': 'node_modules/dragselect/dist/ds.min.js',
    'echarts.js': 'node_modules/echarts/dist/echarts.min.js',
    'imagesloaded.js': 'node_modules/imagesloaded/imagesloaded.pkgd.min.js',
    'justified-gallery.css': 'node_modules/justifiedGallery/dist/css/justifiedGallery.min.css',
    'justified-gallery.js': 'node_modules/justifiedGallery/dist/js/jquery.justifiedGallery.min.js',
    'markdown.css': 'node_modules/github-markdown-css/github-markdown-light.css',
    'masonry.js': 'node_modules/masonry-layout/dist/masonry.pkgd.min.js',
    'viewer.css': 'node_modules/viewerjs/dist/viewer.min.css',
    'viewer.js': 'node_modules/viewerjs/dist/viewer.min.js',
};

export default defineConfig({
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        copyPublicDir: false,
        cssCodeSplit: false,
        assetsInlineLimit: 0,
        rolldownOptions: {
            input: 'resources/js/app.js',
            output: {
                format: 'iife',
                entryFileNames: 'assets/lsky-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
    plugins: [{
        name: 'lsky-page-assets',
        enforce: 'post',
        async generateBundle(_, bundle) {
            const entry = Object.values(bundle).find(file => file.type === 'chunk' && file.isEntry);
            const stylesheet = Object.values(bundle).find(file => file.type === 'asset' && file.fileName.endsWith('.css'));
            if (!entry || !stylesheet) this.error('The application script and stylesheet must be built together.');

            const manifest = {
                'app.js': { file: entry.fileName },
                'app.css': { file: stylesheet.fileName },
            };
            for (const [name, path] of Object.entries(pageAssets)) {
                this.addWatchFile(path);
                const source = await readFile(path);
                const hash = createHash('sha256').update(source).digest('hex').slice(0, 12);
                const dot = name.lastIndexOf('.');
                const file = `assets/${name.slice(0, dot)}-${hash}${name.slice(dot)}`;
                this.emitFile({ type: 'asset', fileName: file, source });
                manifest[name] = { file };
            }
            this.emitFile({ type: 'asset', fileName: 'manifest.json', source: JSON.stringify(manifest, null, 2) });
        },
    }],
});
