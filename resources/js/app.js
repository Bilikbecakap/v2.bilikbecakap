import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { library } from '@fortawesome/fontawesome-svg-core';
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
    faTachometerAlt,
    faUsers,
    faShieldAlt,
    faLock,
    faCog,
    faSignOutAlt,
    faBars,
    faTimes,
    faChevronDown,
    faChevronRight,
    faHistory,
    faEdit,
    faPhone,
    faEnvelope,
    faMapMarkerAlt,
    faCalendar,
    faUser,
    faMoon,
    faSun,
    faHome,
    faDatabase,
    faGlobe,
    faCheck,
    faBook,
    faLanguage,
    faExchangeAlt,
    faNewspaper,
    faBookOpen,
    faQuestionCircle,
    faImages,
    faAddressBook,
    faComments,
    faFlask,
} from '@fortawesome/free-solid-svg-icons';

// Add Font Awesome icons to the library
library.add(
    faTachometerAlt,
    faUsers,
    faShieldAlt,
    faLock,
    faCog,
    faSignOutAlt,
    faBars,
    faTimes,
    faChevronDown,
    faChevronRight,
    faHistory,
    faEdit,
    faPhone,
    faEnvelope,
    faMapMarkerAlt,
    faCalendar,
    faUser,
    faMoon,
    faSun,
    faHome,
    faDatabase,
    faGlobe,
    faCheck,
    faBook,
    faLanguage,
    faExchangeAlt,
    faNewspaper,
    faBookOpen,
    faQuestionCircle,
    faImages,
    faAddressBook,
    faComments,
    faFlask,
);

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => {
        if (title.includes(' - ')) {
            return title;
        }
        return `${title} - ${appName}`;
    },
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(Toast, {
                transition: "Vue-Toastification__bounce",
                maxToasts: 3,
                newestOnTop: true
            })
            .component('font-awesome-icon', FontAwesomeIcon);
        
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});