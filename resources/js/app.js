import './bootstrap';
import '../css/app.css';
import './Styles/layout.css';
import './Styles/components.css';

import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { setupPrimeVue } from '@/plugins/primevue';

const appName = import.meta.env.VITE_APP_NAME || 'Klust';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin);
        app.use(createPinia());
        app.use(ZiggyVue);
        setupPrimeVue(app);
        
        return app.mount(el);
    },
    progress: {
        color: 'var(--color-primary)',
    },
});
