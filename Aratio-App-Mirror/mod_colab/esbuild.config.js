/**
 * esbuild Configuration
 *
 * Bundler configuration for JavaScript modules
 * Bundles Alpine.js, ApexCharts, Vis.js and other dependencies
 */

const esbuild = require('esbuild');
const fs = require('fs');
const path = require('path');

const isProduction = process.env.NODE_ENV === 'production';
const isWatch = process.argv.includes('--watch');

const config = {
    entryPoints: ['public/js/app.js'],
    bundle: true,
    outfile: 'public/js/bundle.js',
    platform: 'browser',
    target: ['es2020'],
    format: 'iife',
    minify: isProduction,
    sourcemap: !isProduction ? 'inline' : false,
    define: {
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development')
    },
    external: [],
    loader: {
        '.js': 'js',
        '.css': 'css',
        '.json': 'json'
    },
    banner: {
        js: '/* A Ratio - Sistema de Gestión de Colaboradores */'
    },
    logLevel: 'info',
};

async function build() {
    try {
        console.log(`\n🚀 Building JavaScript...`);
        console.log(`Mode: ${isProduction ? 'PRODUCTION' : 'DEVELOPMENT'}`);
        console.log(`Watch: ${isWatch ? 'YES' : 'NO'}\n`);

        if (isWatch) {
            const context = await esbuild.context(config);
            await context.watch();
            console.log('👀 Watching for changes...\n');
        } else {
            const result = await esbuild.build(config);

            // Show bundle size
            const stats = fs.statSync(config.outfile);
            const fileSizeInKB = (stats.size / 1024).toFixed(2);
            console.log(`✅ Build complete!`);
            console.log(`📦 Bundle size: ${fileSizeInKB} KB\n`);

            // Show warnings
            if (result.warnings.length > 0) {
                console.log('⚠️  Warnings:');
                result.warnings.forEach(warning => {
                    console.log(`  - ${warning.text}`);
                });
                console.log('');
            }
        }
    } catch (error) {
        console.error('❌ Build failed:', error);
        process.exit(1);
    }
}

build();
