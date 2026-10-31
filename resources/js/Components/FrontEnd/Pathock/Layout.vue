<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref, computed, watch } from "vue";

const showAside = ref(true);
const mobileAside = ref(false);
const form = useForm({});

const page = usePage();

// Close mobile drawer on route change
watch(
    () => page.url,
    () => {
        mobileAside.value = false;
    },
);

const cartCount = computed(() => page.props.cartCount ?? 0);
const siteLogo = computed(() => page.props.site?.logo ?? null);

const navItems = computed(() => [
    {
        label: "Dashboard",
        route: "dashboard.pathock",
        icon: "fa-solid fa-gauge-high",
    },
    // {
    //     label: "Payment Request",
    //     route: "pathock.payment",
    //     icon: "fa-solid fa-file-invoice-dollar",
    // },
    {
        label: "Pathock Refferals",
        route: "pathock.refferals",
        icon: "fa-solid fa-user-group",
    },
    {
        label: "Stock Money",
        route: "stock-money.index",
        icon: "fa-solid fa-arrow-trend-up",
    },
    {
        label: "My Orders",
        route: "pathock.orders",
        icon: "fa-solid fa-box-open",
    },
]);
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- ==================== HEADER ==================== -->
        <header
            class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm"
        >
            <div
                class="flex items-center justify-between gap-4 px-3 sm:px-5 py-2.5"
            >
                <!-- LEFT: logo + toggle -->
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        @click="showAside = !showAside"
                        class="hidden lg:inline-flex items-center justify-center h-10 w-10 rounded-lg text-slate-600 hover:text-green-600 hover:bg-green-50 transition"
                        title="Toggle sidebar"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <button
                        type="button"
                        @click="mobileAside = true"
                        class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg text-slate-600 hover:text-green-600 hover:bg-green-50 transition"
                        title="Open menu"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <Link
                        :href="route('dashboard.pathock')"
                        class="flex items-center gap-2 min-w-0"
                    >
                        <img
                            v-if="siteLogo"
                            :src="`/storage/${siteLogo}`"
                            class="h-12 sm:h-14 w-auto object-contain"
                            alt="Logo"
                        />
                        <span
                            v-else
                            class="text-lg font-extrabold text-green-700 truncate"
                        >
                            {{ $page.props.site?.name ?? "Dashboard" }}
                        </span>
                    </Link>
                </div>

                <!-- RIGHT: actions -->
                <div class="flex items-center gap-1.5 sm:gap-2.5">
                    <a
                        :href="route('home')"
                        target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 h-10 rounded-lg text-sm font-semibold text-slate-600 hover:text-green-700 hover:bg-green-50 transition"
                    >
                        <i class="fa-regular fa-eye"></i>
                        <span class="hidden md:inline">View Site</span>
                    </a>

                    <Link
                        :href="route('pathock.profile')"
                        class="inline-flex items-center gap-1.5 px-3 h-10 rounded-lg text-sm font-semibold text-slate-600 hover:text-green-700 hover:bg-green-50 transition"
                    >
                        <i class="fa-regular fa-user"></i>
                        <span class="hidden md:inline">Profile</span>
                    </Link>

                    <Link
                        :href="route('cart.index')"
                        class="relative inline-flex items-center justify-center h-10 w-10 rounded-lg text-slate-600 hover:text-green-700 hover:bg-green-50 transition"
                    >
                        <i class="fa-solid fa-cart-shopping text-lg"></i>
                        <span
                            v-if="cartCount > 0"
                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white"
                        >
                            {{ cartCount > 99 ? "99+" : cartCount }}
                        </span>
                    </Link>

                    <form
                        @submit.prevent="form.post(route('user.logout'))"
                        class="ml-1"
                    >
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 h-10 px-4 rounded-lg bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-bold shadow-sm hover:shadow transition"
                        >
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- ==================== LAYOUT ==================== -->
        <div class="flex">
            <!-- DESKTOP SIDEBAR -->
            <aside
                v-if="showAside"
                class="hidden lg:block w-64 shrink-0 bg-white border-r border-slate-200 min-h-[calc(100vh-73px)]"
            >
                <nav class="p-3 flex flex-col gap-1">
                    <p
                        class="px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400"
                    >
                        Menu
                    </p>

                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route)"
                        class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition"
                        :class="
                            route().current(item.route)
                                ? 'bg-gradient-to-r from-green-600 to-green-700 text-white shadow-md shadow-green-600/20'
                                : 'text-slate-200 hover:text-green-700 hover:bg-green-50'
                        "
                    >
                        <i
                            :class="[item.icon, 'text-base w-5 text-center']"
                        ></i>
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </nav>

                <!-- Sidebar footer card -->
                <div class="px-3 mt-4">
                    <div
                        class="rounded-xl bg-gradient-to-br from-green-600 to-green-800 text-white p-4 shadow-lg"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-circle-info"></i>
                            <p
                                class="text-xs font-bold uppercase tracking-wide"
                            >
                                Support
                            </p>
                        </div>
                        <p class="text-xs text-green-100 leading-relaxed">
                            Need help? Contact our support team anytime.
                        </p>
                    </div>
                </div>
            </aside>

            <!-- MOBILE DRAWER -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-if="mobileAside"
                        class="fixed inset-0 z-50 bg-slate-900/50 lg:hidden"
                        @click.self="mobileAside = false"
                    >
                        <Transition
                            enter-active-class="transition-transform duration-300 ease-out"
                            enter-from-class="-translate-x-full"
                            enter-to-class="translate-x-0"
                            leave-active-class="transition-transform duration-200 ease-in"
                            leave-from-class="translate-x-0"
                            leave-to-class="-translate-x-full"
                            appear
                        >
                            <aside
                                v-if="mobileAside"
                                class="w-72 max-w-[85vw] h-full bg-white shadow-2xl flex flex-col"
                            >
                                <div
                                    class="flex items-center justify-between px-4 py-3 border-b border-slate-200"
                                >
                                    <img
                                        v-if="siteLogo"
                                        :src="`/storage/${siteLogo}`"
                                        class="h-10 w-auto object-contain"
                                        alt="Logo"
                                    />
                                    <span
                                        v-else
                                        class="text-base font-extrabold text-green-700"
                                    >
                                        {{ $page.props.site?.name ?? "Menu" }}
                                    </span>
                                    <button
                                        type="button"
                                        class="h-9 w-9 rounded-lg text-slate-500 hover:bg-slate-100 transition"
                                        @click="mobileAside = false"
                                    >
                                        <i
                                            class="fa-solid fa-xmark text-lg"
                                        ></i>
                                    </button>
                                </div>

                                <nav
                                    class="p-3 flex flex-col gap-1 flex-1 overflow-y-auto"
                                >
                                    <Link
                                        v-for="item in navItems"
                                        :key="item.route"
                                        :href="route(item.route)"
                                        class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-semibold transition"
                                        :class="
                                            route().current(item.route)
                                                ? 'bg-gradient-to-r from-green-600 to-green-700 text-white shadow-md'
                                                : 'text-slate-200 hover:text-green-700 hover:bg-green-50'
                                        "
                                    >
                                        <i
                                            :class="[
                                                item.icon,
                                                'text-base w-5 text-center',
                                            ]"
                                        ></i>
                                        <span class="truncate">{{
                                            item.label
                                        }}</span>
                                    </Link>

                                    <div
                                        class="mt-2 pt-2 border-t border-slate-200 flex flex-col gap-1"
                                    >
                                        <a
                                            :href="route('home')"
                                            target="_blank"
                                            class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-semibold text-slate-600 hover:text-green-700 hover:bg-green-50 transition"
                                        >
                                            <i
                                                class="fa-regular fa-eye w-5 text-center"
                                            ></i>
                                            View Site
                                        </a>
                                        <Link
                                            :href="route('pathock.profile')"
                                            class="flex items-center gap-3 px-3 py-3 rounded-lg text-sm font-semibold text-slate-600 hover:text-green-700 hover:bg-green-50 transition"
                                        >
                                            <i
                                                class="fa-regular fa-user w-5 text-center"
                                            ></i>
                                            Profile
                                        </Link>
                                    </div>
                                </nav>

                                <div class="p-3 border-t border-slate-200">
                                    <form
                                        @submit.prevent="
                                            form.post(route('user.logout'))
                                        "
                                    >
                                        <button
                                            type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2 h-11 rounded-lg bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-bold shadow-sm"
                                        >
                                            <i
                                                class="fa-solid fa-right-from-bracket"
                                            ></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </aside>
                        </Transition>
                    </div>
                </Transition>
            </Teleport>

            <!-- MAIN CONTENT -->
            <main
                class="flex-1 min-w-0 bg-slate-100 p-3 sm:p-5 lg:p-6 min-h-[calc(100vh-73px)]"
            >
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Smooth scroll for internal scrolling areas */
nav {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
nav::-webkit-scrollbar {
    width: 6px;
}
nav::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
</style>
