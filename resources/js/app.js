import '../css/app.css';
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// ✅ Tambahin ini
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Vuetify
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    // 🔥 FIX DI SINI
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        );

        // inject layout otomatis
        if (page.default.layout === undefined) {
            page.default.layout = (name.startsWith('Auth/') || name === 'Welcome')
                ? null
                : AuthenticatedLayout;
        }

        return page;
    },

    setup({ el, App, props, plugin }) {
        const vuetify = createVuetify({
            components,
            directives,
            theme: {
                defaultTheme: 'dark',
                themes: {
                    light: {
                        colors: { primary: '#534AB7' },
                    },
                    dark: {
                        colors: { primary: '#a855f7', surface: '#1a103c' },
                    }
                },
            },
        });

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)
            .mount(el);
    },

    progress: {
        color: '#4B5563',
    },
});