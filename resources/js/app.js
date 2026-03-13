import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => title.includes(appName) ? title : `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Global handler for non-Inertia responses (e.g. 419 CSRF token mismatch).
// Instead of showing the raw Laravel error page, reload the current page so
// any localStorage draft is restored transparently.
router.on('invalid', (event) => {
    if (event.detail.response.status === 419) {
        event.preventDefault();
        // Refresh the CSRF cookie then reload so the next attempt succeeds
        fetch('/csrf-token')
            .then(r => r.json())
            .then(data => {
                const meta = document.head.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', data.token);
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
            })
            .catch(() => {})
            .finally(() => window.location.reload());
    }
});

