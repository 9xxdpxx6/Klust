import './bootstrap';
import '../css/app.css';
import './Styles/layout.css';
import './Styles/components.css';
import './Styles/responsive.css';

import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { setupPrimeVue } from '@/plugins/primevue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Кластер';

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => {
        const page = resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
        
        // Автоматически применяем layout в зависимости от пути страницы
        if (page && typeof page.then === 'function') {
            return page.then(module => {
                if (module.default && !module.default.layout) {
                    if (name.startsWith('Admin/')) {
                        module.default.layout = AdminLayout;
                    } else if (name.startsWith('Client/Student/')) {
                        module.default.layout = StudentLayout;
                    } else if (name.startsWith('Client/Partner/')) {
                        module.default.layout = PartnerLayout;
                    } else if (name.startsWith('Auth/')) {
                        module.default.layout = GuestLayout;
                    } else if (name.startsWith('Notifications/')) {
                        // Определяем layout на основе URL
                        const path = window.location.pathname;
                        if (path.startsWith('/admin/notifications')) {
                            module.default.layout = AdminLayout;
                        } else if (path.startsWith('/partner/notifications')) {
                            module.default.layout = PartnerLayout;
                        } else {
                            module.default.layout = StudentLayout; // По умолчанию для /student/notifications
                        }
                    }
                }
                return module;
            });
        } else if (page && page.default) {
            if (!page.default.layout) {
                if (name.startsWith('Admin/')) {
                    page.default.layout = AdminLayout;
                } else if (name.startsWith('Client/Student/')) {
                    page.default.layout = StudentLayout;
                } else if (name.startsWith('Client/Partner/')) {
                    page.default.layout = PartnerLayout;
                } else if (name.startsWith('Auth/')) {
                    page.default.layout = GuestLayout;
                } else if (name.startsWith('Notifications/')) {
                    // Определяем layout на основе URL
                    const path = window.location.pathname;
                    if (path.startsWith('/admin/notifications')) {
                        page.default.layout = AdminLayout;
                    } else if (path.startsWith('/partner/notifications')) {
                        page.default.layout = PartnerLayout;
                    } else {
                        page.default.layout = StudentLayout; // По умолчанию для /student/notifications
                    }
                }
            }
        }
        
        return page;
    },
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
