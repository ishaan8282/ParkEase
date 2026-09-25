import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue, route } from '../../vendor/tightenco/ziggy'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap-icons/font/bootstrap-icons.css'
import '../css/pe-styles.css';

createInertiaApp({
    title: (title) => `${title} - ParkEase`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
        app.use(plugin)
        app.use(ZiggyVue, props.initialPage.props.ziggy)
        app.config.globalProperties.route = route
        app.mount(el)
    },
    progress: { color: '#0d6efd' }
})
