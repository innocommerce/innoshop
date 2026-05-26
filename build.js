/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *
 * InnoShop Asset Build Script (Vite)
 *
 * Each entry is compiled independently via `npx vite build` with environment variables,
 * producing a single CSS or JS bundle per module. JS outputs are renamed from Vite's
 * default `index.js` to the desired filename.
 *
 * Usage:
 *   npm run build                        Build all core modules (front + panel + install)
 *   npm run prod                         Alias for build
 *   TARGET=front npm run build           Build only front module
 *   TARGET=panel npm run build           Build only panel module
 *   TARGET=install npm run build         Build only install module
 *   THEME=fdsodental npm run prod        Build only theme (skips core modules)
 *   TARGET=panel THEME=fdsodental npm run prod  Build panel + theme
 */

import { execSync } from 'child_process';
import fs from 'fs';

const theme = process.env.THEME || '';
const target = process.env.TARGET || '';
const startTime = Date.now();

const coreEntries = [
    { name: 'front/css', input: 'innopacks/front/resources/css/app.scss', outDir: 'public/build/front/css', outputName: 'app', group: 'front' },
    { name: 'front/js',  input: 'innopacks/front/resources/js/app.js',   outDir: 'public/build/front/js',  outputName: 'app', group: 'front' },
    { name: 'panel/css', input: 'innopacks/panel/resources/css/app.scss', outDir: 'public/build/panel/css', outputName: 'app', group: 'panel' },
    { name: 'panel/js',  input: 'innopacks/panel/resources/js/app.js',    outDir: 'public/build/panel/js',  outputName: 'app', group: 'panel' },
    { name: 'install/css', input: 'innopacks/install/resources/css/app.scss', outDir: 'public/build/install/css', outputName: 'app', group: 'install' },
];

// Filter by TARGET (front/panel/install), or skip core when building a theme
const entries = theme ? [] : coreEntries.filter(e => !target || e.group === target);

// Theme entries
if (theme) {
    const themeDir = `themes/${theme}`;
    const themeOut = `public/static/themes/${theme}`;

    ['css', 'js'].forEach(dir => {
        const p = `${themeOut}/${dir}`;
        if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
    });

    if (fs.existsSync(`${themeDir}/css/app.scss`))
        entries.push({ name: 'theme/css', input: `${themeDir}/css/app.scss`, outDir: `${themeOut}/css`, outputName: 'app' });
    if (fs.existsSync(`${themeDir}/js/app.js`))
        entries.push({ name: 'theme/js', input: `${themeDir}/js/app.js`, outDir: `${themeOut}/js`, outputName: 'app' });
}

if (entries.length === 0) {
    console.log('No entries to build.');
    process.exit(0);
}

let failed = 0;
for (const entry of entries) {
    try {
        execSync(
            `BUILD_INPUT="${entry.input}" BUILD_OUTDIR="${entry.outDir}" BUILD_OUTPUT_NAME="${entry.outputName}" npx vite build`,
            { stdio: 'pipe', env: { ...process.env, BUILD_INPUT: entry.input, BUILD_OUTDIR: entry.outDir, BUILD_OUTPUT_NAME: entry.outputName } }
        );
        // Vite lib IIFE always outputs as index.js — rename to desired name
        if (entry.input.endsWith('.js')) {
            const idx = `${entry.outDir}/index.js`;
            const target = `${entry.outDir}/${entry.outputName}.js`;
            if (fs.existsSync(idx) && idx !== target) {
                fs.renameSync(idx, target);
            }
        }
        console.log(`  ✓ ${entry.name}`);
    } catch (e) {
        console.error(`  ✗ ${entry.name}`);
        const err = e.stderr?.toString() || '';
        err.split('\n').filter(l => l.includes('Error')).slice(0, 3).forEach(l => console.error(`    ${l}`));
        failed++;
    }
}

// Theme distribution
if (theme) {
    const targetDir = `public/static/themes/${theme}`;
    const distDir = `themes/${theme}/public`;
    ['css', 'js'].forEach(dir => {
        const src = `${targetDir}/${dir}`;
        const dest = `${distDir}/${dir}`;
        if (fs.existsSync(src)) {
            fs.mkdirSync(dest, { recursive: true });
            fs.readdirSync(src).forEach(file => fs.copyFileSync(`${src}/${file}`, `${dest}/${file}`));
        }
    });
}

// Linux permissions
if (process.platform === 'linux' && fs.existsSync('public')) {
    try { execSync('chmod -R 755 public', { stdio: 'pipe' }); } catch {}
    try { execSync('chown -R www:www public', { stdio: 'pipe' }); } catch {}
}

const elapsed = ((Date.now() - startTime) / 1000).toFixed(2);
console.log(`\nDone in ${elapsed}s${failed ? ` (${failed} failed)` : ''}`);
