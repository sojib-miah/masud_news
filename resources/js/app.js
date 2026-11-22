import "./bootstrap";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "ziggy-js";

const pages = import.meta.glob("./Pages/**/*.vue");

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, pages),

    title: (title) =>
        title ? `${title} - Bangladesh News Today` : "Bangladesh News Today",

    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },

    progress: {
        color: "#fff",
        showSpinner: false,
    },

    defaults: {
        future: {
            useDialogForErrorModal: true,
        },
    },
});
