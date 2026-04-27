import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/web3/Web3App.jsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        target: 'es2018',
        minify: 'esbuild',
        cssCodeSplit: true,
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    if (id.includes('chart.js')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
    optimizeDeps: {
        include: ['chart.js', 'wagmi', 'viem', '@wagmi/core', '@wagmi/connectors', '@tanstack/react-query', 'ethers'],
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
