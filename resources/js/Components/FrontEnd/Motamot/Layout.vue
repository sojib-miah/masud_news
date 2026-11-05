<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";

const page = usePage();
const user = page.props.auth.user;

const form = useForm({});

const avatarSrc = computed(() =>
    user?.image ? `/storage/${user.image}` : "/storage/images/user.png",
);

// ==================== STATE ====================
const showAside = ref(true); // desktop sidebar (collapsible)
const mobileMenuOpen = ref(false); // mobile drawer

const showMenu = ref(false);

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

const isActiveNews = computed(
    () =>
        route().current("news.all") ||
        route().current("featured.news") ||
        route().current("breaking.news") ||
        route().current("pending.news"),
);

const menuVisible = computed(() => showMenu.value || isActiveNews.value);

// Close mobile drawer when navigating
watch(
    () => page.url,
    () => {
        mobileMenuOpen.value = false;
    },
);

// Lock body scroll when mobile menu is open
watch(mobileMenuOpen, (open) => {
    if (typeof document !== "undefined") {
        document.body.style.overflow = open ? "hidden" : "";
    }
});

// ==================== NAV ITEMS ====================
const navItems = [
    {
        label: "Dashboard",
        href: route("dashboard.motamot"),
        active: () => route().current("dashboard.motamot"),
        icon: "fa-solid fa-gauge-high",
    },
    {
        label: "Motamot",
        href: route("motamot.index"),
        active: () => route().current("motamot.index"),
        icon: "fa-solid fa-manat-sign",
    },
];
</script>

<template>
    <div class="min-h-screen bg-slate-100 flex flex-col">
        <!-- ==================== HEADER ==================== -->
        <header
            class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm"
        >
            <div
                class="flex items-center justify-between gap-3 px-3 sm:px-4 lg:px-6 h-16"
            >
                <!-- Left: menu button + logo -->
                <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                    <!-- Mobile hamburger -->
                    <button
                        type="button"
                        @click="mobileMenuOpen = true"
                        class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition"
                        aria-label="Open menu"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <!-- Desktop sidebar toggle -->
                    <button
                        type="button"
                        @click="showAside = !showAside"
                        class="hidden lg:flex w-10 h-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition"
                        aria-label="Toggle sidebar"
                    >
                        <i
                            :class="
                                showAside
                                    ? 'fa-solid fa-bars-staggered'
                                    : 'fa-solid fa-bars'
                            "
                            class="text-lg"
                        ></i>
                    </button>

                    <Link
                        :href="route('dashboard.motamot')"
                        class="flex items-center shrink-0"
                    >
                        <img
                            :src="`/storage/${$page.props.site?.logo}`"
                            class="h-10 sm:h-12 lg:h-14 w-auto object-contain"
                            alt="Logo"
                        />
                    </Link>
                </div>

                <!-- Right: actions -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <a
                        :href="route('home')"
                        target="_blank"
                        class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-green-600 transition"
                    >
                        <i class="fa-regular fa-eye"></i>
                        <span>View Site</span>
                    </a>

                    <!-- User chip -->
                    <Link :href="route('motamot.profile')">
                        <div
                            class="hidden lg:flex items-center gap-2 pl-3 ml-1 border-l border-slate-200"
                        >
                            <div
                                class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center"
                            >
                                <!-- {{ (user?.name || "U").charAt(0) }} -->
                                <img
                                    :src="avatarSrc"
                                    alt=""
                                    class="w-9 h-9 rounded-full object-cover"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/storage/images/user.png')
                                    "
                                />
                            </div>
                            <div class="leading-tight">
                                <p
                                    class="text-xs font-bold text-slate-700 max-w-[120px] truncate"
                                >
                                    {{ user?.name }}
                                </p>
                                <p class="text-[10px] text-slate-400">
                                    Motamot
                                </p>
                            </div>
                        </div>
                    </Link>

                    <!-- Logout -->
                    <form
                        @submit.prevent="form.post(route('user.logout'))"
                        class="ml-1"
                    >
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-white bg-red-500 hover:bg-red-600 shadow-sm transition"
                        >
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- ==================== BODY ==================== -->
        <div class="flex flex-1">
            <!-- ==================== DESKTOP SIDEBAR ==================== -->
            <aside
                v-if="showAside"
                class="hidden lg:flex flex-col w-64 border-r border-slate-200 shrink-0"
            >
                <nav class="flex flex-col gap-1 p-3">
                    <p
                        class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 py-2"
                    >
                        Menu
                    </p>

                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition',
                            item.active()
                                ? 'bg-green-600 text-white shadow-sm'
                                : 'text-slate-100 hover:bg-slate-100 hover:text-green-700',
                        ]"
                    >
                        <i :class="item.icon" class="w-5 text-center"></i>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="mt-auto p-3 border-t border-slate-100">
                    <p class="text-[11px] text-slate-300 text-center">
                        Copyright &copy; {{ new Date().getFullYear() }}
                    </p>
                </div>
            </aside>

            <!-- ==================== MOBILE DRAWER ==================== -->
            <Teleport to="body">
                <!-- Overlay -->
                <div
                    v-if="mobileMenuOpen"
                    class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm lg:hidden"
                    @click="mobileMenuOpen = false"
                ></div>

                <!-- Drawer -->
                <aside
                    :class="[
                        'fixed top-0 left-0 z-50 h-full w-72 max-w-[85vw] shadow-2xl flex flex-col transition-transform duration-300 lg:hidden',
                        mobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
                    ]"
                >
                    <div
                        class="flex items-center justify-between px-4 h-16 border-b border-slate-200"
                    >
                        <Link
                            :href="route('dashboard.motamot')"
                            class="flex items-center"
                        >
                            <img
                                :src="`/storage/${$page.props.site?.logo}`"
                                class="h-10 w-auto object-contain"
                                alt="Logo"
                            />
                        </Link>
                        <button
                            type="button"
                            @click="mobileMenuOpen = false"
                            class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-100 hover:bg-slate-100 hover:text-slate-700 transition"
                            aria-label="Close menu"
                        >
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <nav class="flex flex-col gap-1 p-3 overflow-y-auto">
                        <p
                            class="text-[11px] font-bold uppercase tracking-wider text-slate-300 px-3 py-2"
                        >
                            Menu
                        </p>

                        <Link
                            v-for="item in navItems"
                            :key="item.label"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition',
                                item.active()
                                    ? 'bg-green-600 text-white shadow-sm'
                                    : 'text-slate-100 hover:bg-slate-100 hover:text-green-700',
                            ]"
                        >
                            <i :class="item.icon" class="w-5 text-center"></i>
                            <span>{{ item.label }}</span>
                        </Link>

                        <p
                            class="text-[11px] font-bold uppercase tracking-wider text-slate-300 px-3 py-2 mt-3"
                        >
                            Account
                        </p>

                        <a
                            :href="route('home')"
                            target="_blank"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-100 hover:bg-slate-100 hover:text-green-700 transition"
                        >
                            <i class="fa-regular fa-eye w-5 text-center"></i>
                            <span>View Site</span>
                        </a>

                        <Link
                            :href="route('motamot.profile')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-100 hover:bg-slate-100 hover:text-green-700 transition"
                        >
                            <i class="fa-regular fa-user w-5 text-center"></i>
                            <span>Profile</span>
                        </Link>
                    </nav>

                    <div class="mt-auto p-3 border-t border-slate-100">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold uppercase"
                            >
                                {{ (user?.name || "U").charAt(0) }}
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="text-sm font-bold text-slate-100 truncate"
                                >
                                    {{ user?.name }}
                                </p>
                                <p class="text-xs text-slate-100 truncate">
                                    {{ user?.email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </Teleport>

            <!-- ==================== MAIN ==================== -->
            <main class="flex-1 w-full min-w-0 p-3 sm:p-4 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
ul,
li {
    list-style: none;
}
</style>
