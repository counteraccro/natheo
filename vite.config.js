import { defineConfig } from 'vite';
import { resolve } from 'path';
import vue from '@vitejs/plugin-vue';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig(({ command }) => ({
  plugins: [
    symfonyPlugin({
      stimulus: true, // Active le support Stimulus
    }),
    vue(),
  ],

  root: '.',
  base: command === 'serve' ? '/' : '/build/',

  build: {
    manifest: true,
    emptyOutDir: true,
    outDir: 'public/build',
    assetsDir: '',
    rollupOptions: {
      input: {
        // Vos différents points d'entrée
        admin_red: './assets/styles/app_admin_red.scss',
        admin_purple: './assets/styles/app_admin_purple.scss',
        admin_orange: './assets/styles/app_admin_orange.scss',
        install_css: './assets/styles/app_install.scss',
        email: './assets/styles/app_email.scss',
        admin: './assets/app_admin.js',
        front_natheo_horizon: './assets/app_front.js',
        install: './assets/app_install.js',
      },
      output: {
        manualChunks: undefined, // Équivalent de splitEntryChunks
      },
    },
  },

  server: {
    host: '0.0.0.0', // Permet d'accéder depuis n'importe quel domaine local
    cors: true,
    strictPort: false,
    port: 5173,
    https: false,
    origin: 'http://dev.natheo:5173',
    // Optionnel : watch des fichiers twig pour le HMR
    watch: {
      usePolling: false,
      ignored: ['**/node_modules/**', '**/public/build/**'],
    },
  },

  resolve: {
    alias: {
      '@': resolve(__dirname, './assets'),
      // Alias pour Vue 3 avec support complet (Options API + Composition API + template compiler)
      vue: 'vue/dist/vue.esm-bundler.js',
    },
    extensions: ['.js', '.json', '.jsx', '.ts', '.tsx', '.vue'],
  },

  // Configuration spécifique pour Vue 3
  define: {
    __VUE_OPTIONS_API__: true,
    __VUE_PROD_DEVTOOLS__: false,
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
  },

  css: {
    preprocessorOptions: {
      scss: {
        // Options SASS si nécessaire
        additionalData: ``, // Ajoutez vos variables globales ici si besoin
      },
    },
  },

  // Optimisation des dépendances
  optimizeDeps: {
    include: ['vue'],
  },
}));
