/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *
 * Vite Configuration for InnoShop
 *
 * This config is designed for single-entry builds driven by environment variables,
 * invoked once per entry by build.js. It does NOT use Vite's multi-input or dev server.
 *
 * Environment variables:
 *   BUILD_INPUT       — Source file path (e.g. innopacks/front/resources/css/app.scss)
 *   BUILD_OUTDIR      — Output directory (e.g. public/build/front/css)
 *   BUILD_OUTPUT_NAME — Output filename for JS entries (default: 'app')
 *   THEME             — Theme name, used to resolve @theme alias (default: 'scandihome')
 *
 * Alias resolution:
 *   @front  → innopacks/front/resources/
 *   @theme  → themes/{THEME}/
 *   ~       → node_modules/ (for legacy SCSS imports like ~bootstrap)
 *
 * Build modes:
 *   CSS build — Uses rollupOptions.input, outputs unhashed assets via assetFileNames
 *   JS build  — Uses lib mode (IIFE), outputs via fileName, build.js renames index.js
 */

import { defineConfig } from 'vite';
import { resolve } from 'path';
import fs from 'fs';

const theme = process.env.THEME || 'scandihome';
const frontResources = resolve('innopacks/front/resources');

function tildeImporter() {
    const aliases = {
        '@front': frontResources,
        '@theme': resolve(`themes/${theme}`),
    };
    return {
        canonicalize(url) {
            if (url.startsWith('~')) {
                const stripped = url.slice(1);
                for (const [alias, target] of Object.entries(aliases)) {
                    if (stripped === alias || stripped.startsWith(alias + '/')) {
                        return new URL(`file://${target}${stripped.slice(alias.length)}`);
                    }
                }
                return new URL(`file://${resolve('node_modules', stripped)}`);
            }
            if (url.startsWith('node_modules/')) {
                return new URL(`file://${resolve(url)}`);
            }
            return null;
        },
        load(canonicalUrl) {
            const filePath = decodeURIComponent(canonicalUrl.pathname);
            let resolved = filePath;
            if (!fs.existsSync(resolved)) {
                for (const ext of ['.scss', '.sass', '.css']) {
                    if (fs.existsSync(resolved + ext)) { resolved = resolved + ext; break; }
                    const dir = resolved.substring(0, resolved.lastIndexOf('/') + 1);
                    const base = resolved.substring(resolved.lastIndexOf('/') + 1);
                    if (fs.existsSync(dir + '_' + base + ext)) { resolved = dir + '_' + base + ext; break; }
                }
            }
            return { contents: fs.readFileSync(resolved, 'utf-8'), syntax: resolved.endsWith('.sass') ? 'indented' : 'scss' };
        },
    };
}

// Config for single entry build, driven by env vars:
//   BUILD_INPUT — source file path
//   BUILD_OUTDIR — output directory
const input = process.env.BUILD_INPUT;
const outDir = process.env.BUILD_OUTDIR;

if (!input || !outDir) {
    throw new Error('BUILD_INPUT and BUILD_OUTDIR env vars required. Use build.js for full builds.');
}

const isJS = input.endsWith('.js');
const outputName = process.env.BUILD_OUTPUT_NAME || 'app';

export default defineConfig({
    publicDir: false,
    build: {
        emptyOutDir: false,
        lib: isJS ? {
            entry: resolve(input),
            name: 'app',
            formats: ['iife'],
            fileName: () => outputName,
        } : undefined,
        rollupOptions: {
            input: isJS ? undefined : resolve(input),
            output: {
                dir: outDir,
                entryFileNames: '[name].js',
                assetFileNames: (info) => {
                    // For CSS-only builds, output the main asset without hash
                    const name = info.name || 'app';
                    return `${name}${info.ext || ''}`;
                },
            },
        },
    },
    resolve: {
        alias: {
            '@front': frontResources,
            '@theme': resolve(`themes/${theme}`),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
                loadPaths: [resolve('node_modules')],
                importers: [tildeImporter()],
            },
        },
    },
});
