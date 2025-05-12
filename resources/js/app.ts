import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, type DefineComponent, h } from "vue";
import { createPinia } from "pinia";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import ToastPlugin from "vue-toast-notification";
import "vue-toast-notification/dist/theme-bootstrap.css";
// 自分の要素内以外をクリックしたらイベントが発生するやつ。 Alpine.jsの@click.outsideと同じ
import clickOutside from "@/Directives/clickOutSide.js";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(createPinia())
            .use(ToastPlugin, {
                position: "top-right",
                duration: 5000,
            })
            .directive("click-outside", clickOutside);

        vueApp.mount(el);

        return vueApp;
    },
    progress: {
        color: "#4B5563",
    },
});
