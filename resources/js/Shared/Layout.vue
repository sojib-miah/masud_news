<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import RecursiveMenu from "@/Shared/RecursiveMenu.vue";
import { route } from "ziggy-js";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import Header from "../Components/Header.vue";
import Language from "../Components/Language.vue";
import Notification from "../Components/Notification.vue";
import AccessibilityPanel from "../Components/AccessibilityPanel.vue";
import FontSizeBar from "../Components/FontSizeBar.vue";
import DarkModeToggle from "../Components/DarkModeToggle.vue";

const page = usePage();
const props = defineProps({
    menus: Object,
    auth: Object,
});

const auth = props.auth || page.props.auth || {};
const menus = props.menus || page.props.menus || {};
const currentYear = new Date().getFullYear();

// ==================== STATE ====================
const collapsed = ref(false); // desktop sidebar collapse
const mobileMenuOpen = ref(false); // mobile drawer
const searchTerm = ref("");
const animatedPlaceholder = ref("");

// ==================== SEARCH FILTER ====================
function filterRecursive(menusArray, term) {
    return menusArray
        .map((menu) => {
            const children = menu.children
                ? filterRecursive(menu.children, term)
                : [];

            const matches = menu.title
                .toLowerCase()
                .includes(term.toLowerCase());

            if (matches || children.length) {
                return { ...menu, children };
            }
            return null;
        })
        .filter(Boolean);
}

const filteredMenus = computed(() => {
    if (!searchTerm.value) {
        return menus.left;
    }
    return filterRecursive(menus.left, searchTerm.value);
});

// ==================== ANIMATED PLACEHOLDER ====================
const placeholderText = "Search Menu...";
let intervalId;
onMounted(() => {
    let i = 0;
    let typingForward = true;

    intervalId = setInterval(() => {
        if (typingForward) {
            animatedPlaceholder.value = placeholderText.slice(0, i + 1);
            i++;
            if (i === placeholderText.length) {
                typingForward = false;
            }
        } else {
            animatedPlaceholder.value = placeholderText.slice(0, i);
            i--;
            if (i === 0) {
                typingForward = true;
            }
        }
    }, 200);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

// Close mobile drawer on route change
watch(
    () => page.url,
    () => {
        mobileMenuOpen.value = false;
    },
);

// Lock body scroll when mobile drawer is open
watch(mobileMenuOpen, (open) => {
    if (typeof document !== "undefined") {
        document.body.style.overflow = open ? "hidden" : "";
    }
});

// ==================== EXTERNAL LINK ====================
const public_url = "https://sojib.comitsbd.com/";
function newTab(e) {
    e.preventDefault();
    window.open(public_url, "_blank");
}
</script>

<template>
    <div class="min-h-screen flex flex-col bg-slate-50">
        <!-- ==================== TOP HEADER ==================== -->
        <header
            class="sticky top-0 z-40 bg-white border-b border-slate-200 shadow-sm"
        >
            <div
                class="flex items-center justify-between gap-3 px-3 sm:px-4 lg:px-6 h-16"
            >
                <!-- Left: hamburger (mobile) + collapse (desktop) + logo -->
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <!-- Mobile hamburger -->
                    <button
                        type="button"
                        @click="mobileMenuOpen = true"
                        class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition"
                        aria-label="Open menu"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>

                    <!-- Desktop collapse -->
                    <button
                        type="button"
                        @click="collapsed = !collapsed"
                        class="hidden lg:flex w-10 h-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 transition"
                        aria-label="Toggle sidebar"
                    >
                        <i
                            :class="
                                collapsed
                                    ? 'fa-solid fa-bars'
                                    : 'fa-solid fa-bars-staggered'
                            "
                            class="text-lg"
                        ></i>
                    </button>

                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center shrink-0"
                    >
                        <img
                            :src="`/storage/${$page.props.site?.logo}`"
                            class="h-10 sm:h-12 w-auto object-contain"
                            alt="ComitsBD Admin"
                        />
                    </Link>
                </div>

                <!-- Top menus (desktop only) -->
                <nav class="hidden xl:flex items-center gap-1">
                    <RecursiveMenu
                        v-for="menu in menus.top"
                        :key="menu.id"
                        :menu="menu"
                        direction="horizontal"
                    />
                </nav>

                <!-- Right actions -->
                <div class="flex items-center gap-1 sm:gap-2">
                    <a
                        target="_blank"
                        :href="route('home')"
                        class="hidden md:inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-teal-600 transition"
                    >
                        <i class="fa-solid fa-globe"></i>
                        <span>Visit site</span>
                    </a>

                    <div class="flex items-center gap-1 sm:gap-2">
                        <Language class="hidden" />
                        <DarkModeToggle />
                        <Header />
                        <Notification />
                    </div>
                </div>
            </div>
        </header>

        <!-- ==================== BODY ==================== -->
        <div class="flex flex-1 min-h-0">
            <!-- ==================== DESKTOP SIDEBAR ==================== -->
            <aside
                :class="[
                    'hidden lg:flex flex-col bg-white border-r border-slate-200 shrink-0 transition-all duration-300',
                    collapsed ? 'w-20' : 'w-64',
                ]"
            >
                <!-- Search -->
                <div class="p-3 border-b border-slate-100">
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                        >
                            <i
                                class="fa-solid fa-magnifying-glass text-slate-400 text-sm"
                            ></i>
                        </div>
                        <input
                            v-show="!collapsed"
                            type="search"
                            v-model="searchTerm"
                            :placeholder="animatedPlaceholder"
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 focus:bg-white transition text-black"
                        />
                        <button
                            v-show="collapsed"
                            type="button"
                            class="w-full flex items-center justify-center py-2 rounded-lg text-slate-400 hover:bg-slate-100 transition"
                            title="Expand to search"
                            @click="collapsed = false"
                        >
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>

                <!-- Menu -->
                <nav
                    class="flex-1 overflow-y-auto p-2"
                    :class="collapsed ? 'text-center' : ''"
                >
                    <RecursiveMenu
                        v-for="menu in filteredMenus"
                        :key="menu.id"
                        :menu="menu"
                        direction="vertical"
                        :collapsed="collapsed"
                    />
                </nav>

                <!-- Collapse toggle at bottom -->
                <div class="p-2 border-t border-slate-100">
                    <button
                        type="button"
                        @click="collapsed = !collapsed"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-200 hover:bg-slate-100 hover:text-slate-700 transition"
                    >
                        <i
                            :class="
                                collapsed
                                    ? 'fa-solid fa-angles-right'
                                    : 'fa-solid fa-angles-left'
                            "
                        ></i>
                        <span v-show="!collapsed">Collapse</span>
                    </button>
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
                        'fixed top-0 left-0 z-50 h-full w-72 max-w-[85vw] bg-white shadow-2xl flex flex-col transition-transform duration-300 lg:hidden',
                        mobileMenuOpen ? 'translate-x-0' : '-translate-x-full',
                    ]"
                >
                    <!-- Drawer header -->
                    <div
                        class="flex items-center justify-between px-4 h-16 border-b border-slate-200 shrink-0"
                    >
                        <Link
                            :href="route('admin.dashboard')"
                            class="flex items-center"
                            @click="mobileMenuOpen = false"
                        >
                            <img
                                :src="`/storage/${$page.props.site?.logo}`"
                                class="h-10 w-auto object-contain"
                                alt="ComitsBD Admin"
                            />
                        </Link>
                        <button
                            type="button"
                            @click="mobileMenuOpen = false"
                            class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-200 hover:bg-slate-100 transition hover:text-slate-700"
                            aria-label="Close menu"
                        >
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <!-- Drawer search -->
                    <div class="p-3 border-b border-slate-100">
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                            >
                                <i
                                    class="fa-solid fa-magnifying-glass text-slate-400 text-sm"
                                ></i>
                            </div>
                            <input
                                type="search"
                                v-model="searchTerm"
                                :placeholder="animatedPlaceholder"
                                class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 focus:bg-white transition"
                            />
                        </div>
                    </div>

                    <!-- Drawer menus -->
                    <nav class="flex-1 overflow-y-auto p-2">
                        <RecursiveMenu
                            v-for="menu in filteredMenus"
                            :key="menu.id"
                            :menu="menu"
                            direction="vertical"
                            :collapsed="false"
                        />

                        <!-- Top menus fallback for mobile -->
                        <div
                            v-if="menus.top?.length"
                            class="mt-3 pt-3 border-t border-slate-100"
                        >
                            <p
                                class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 py-2"
                            >
                                Quick Links
                            </p>
                            <RecursiveMenu
                                v-for="menu in menus.top"
                                :key="menu.id"
                                :menu="menu"
                                direction="vertical"
                                :collapsed="false"
                            />
                        </div>
                    </nav>

                    <!-- Drawer footer -->
                    <div
                        class="p-3 border-t border-slate-100 space-y-2 shrink-0"
                    >
                        <a
                            target="_blank"
                            :href="route('home')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-200 hover:bg-slate-100 hover:text-teal-600 transition"
                            @click="mobileMenuOpen = false"
                        >
                            <i class="fa-solid fa-globe w-5 text-center"></i>
                            <span>Visit Site</span>
                        </a>
                    </div>
                </aside>
            </Teleport>

            <!-- ==================== MAIN ==================== -->
            <main
                class="flex-1 w-full min-w-0 p-3 sm:p-4 lg:p-6 overflow-y-auto"
            >
                <slot />
            </main>
        </div>

        <!-- ==================== FOOTER ==================== -->
        <footer class="bg-white border-t border-slate-200 px-3 sm:px-6 py-4">
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-3"
            >
                <nav
                    class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1"
                >
                    <RecursiveMenu
                        v-for="menu in menus.footer"
                        :key="menu.id"
                        :menu="menu"
                        direction="horizontal"
                    />
                </nav>

                <p class="text-xs sm:text-sm text-slate-500 text-center">
                    All Rights Reserved
                    <button
                        type="button"
                        @click="newTab"
                        class="text-teal-600 hover:underline font-semibold"
                    >
                        Sojib
                    </button>
                    | {{ currentYear }}
                </p>
            </div>
        </footer>

        <!-- ==================== GLOBAL PANELS ==================== -->
        <AccessibilityPanel />
        <FontSizeBar />
    </div>
</template>
