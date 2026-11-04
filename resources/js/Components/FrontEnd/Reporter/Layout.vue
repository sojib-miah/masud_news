<script setup>
import { Link, useForm, usePage, Head } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";

const page = usePage();
const user = computed(() => page.props.auth.user);

// ==================== STATE ====================
const showAsideDesktop = ref(true); // Desktop toggle
const showAsideMobile = ref(false); // Mobile overlay toggle
const showMenu = ref(false); // Sub-menu toggle

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

// ==================== ACTIVE STATES ====================
const isActiveNews = computed(
    () =>
        route().current("news.all") ||
        route().current("featured.news") ||
        route().current("breaking.news") ||
        route().current("pending.news"),
);

const menuVisible = computed(() => showMenu.value || isActiveNews.value);

// Check if a link is active for styling
const isActive = (routeName) => route().current(routeName);

// ==================== LOGOUT ====================
const form = useForm({});
const logout = () => {
    form.post(route("user.logout"));
};

// Close mobile menu when route changes
watch(
    () => page.url,
    () => {
        showAsideMobile.value = false;
    },
);
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-robo flex">
        <!-- ==================== MOBILE OVERLAY ==================== -->
        <div
            v-if="showAsideMobile"
            @click="showAsideMobile = false"
            class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden backdrop-blur-sm transition-opacity"
        ></div>

        <!-- ==================== SIDEBAR ==================== -->
        <aside
            class="fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 w-64 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:flex-shrink-0"
            :class="[
                showAsideMobile
                    ? 'translate-x-0 shadow-2xl'
                    : '-translate-x-full',
                !showAsideDesktop && 'lg:hidden',
            ]"
        >
            <!-- Sidebar Header (Logo) -->
            <div
                class="h-16 flex items-center justify-between px-4 border-b border-slate-100 flex-shrink-0"
            >
                <Link
                    :href="route('dashboard.reporter')"
                    class="flex items-center gap-2 overflow-hidden"
                >
                    <img
                        :src="`/storage/${page.props.site?.logo}`"
                        class="h-10 object-contain"
                        alt="Logo"
                    />
                </Link>
                <button
                    @click="showAsideMobile = false"
                    class="lg:hidden text-slate-200 hover:text-slate-300 p-2"
                >
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <div
                class="flex-1 overflow-y-auto custom-scroll py-4 px-3 space-y-1"
            >
                <!-- Dashboard -->
                <Link
                    :href="route('dashboard.reporter')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors duration-200"
                    :class="
                        isActive('dashboard.reporter')
                            ? 'bg-green-50 text-green-700'
                            : 'text-slate-100 hover:bg-white hover:text-slate-900'
                    "
                >
                    <i
                        class="fa-solid fa-gauge w-5 text-center"
                        :class="
                            isActive('dashboard.reporter')
                                ? 'text-green-600'
                                : 'text-slate-300'
                        "
                    ></i>
                    Dashboard
                </Link>

                <!-- News Dropdown -->
                <div>
                    <button
                        @click="toggleMenu"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors duration-200"
                        :class="
                            isActiveNews
                                ? 'text-green-100 bg-green-50/50'
                                : 'text-slate-100 hover:bg-white hover:text-slate-900'
                        "
                    >
                        <div class="flex items-center gap-3">
                            <i
                                class="fa-regular fa-newspaper w-5 text-center"
                                :class="
                                    isActiveNews
                                        ? 'text-green-600'
                                        : 'text-slate-300'
                                "
                            ></i>
                            News
                        </div>
                        <i
                            class="fa-solid fa-chevron-down text-xs transition-transform duration-300"
                            :class="{ 'rotate-180': menuVisible }"
                        ></i>
                    </button>

                    <!-- Sub Menu -->
                    <div
                        v-show="menuVisible"
                        class="mt-1 ml-4 pl-3 border-l-2 border-slate-100 space-y-1 overflow-hidden transition-all duration-300"
                    >
                        <Link
                            :href="route('news.all')"
                            class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="
                                isActive('news.all')
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-slate-100 hover:text-slate-900 hover:bg-slate-50'
                            "
                        >
                            All News
                        </Link>
                        <Link
                            :href="route('featured.news')"
                            class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="
                                isActive('featured.news')
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-slate-100 hover:text-slate-900 hover:bg-slate-50'
                            "
                        >
                            Featured News
                        </Link>
                        <Link
                            :href="route('breaking.news')"
                            class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="
                                isActive('breaking.news')
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-slate-100 hover:text-slate-900 hover:bg-slate-50'
                            "
                        >
                            Breaking News
                        </Link>
                        <Link
                            :href="route('pending.news')"
                            class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                            :class="
                                isActive('pending.news')
                                    ? 'bg-green-50 text-green-700'
                                    : 'text-slate-100 hover:text-slate-900 hover:bg-slate-50'
                            "
                        >
                            Pending News
                        </Link>
                    </div>
                </div>

                <!-- Other Links -->
                <Link
                    :href="route('schedule.news')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors duration-200"
                    :class="
                        isActive('schedule.news')
                            ? 'bg-green-50 text-green-700'
                            : 'text-slate-100 hover:bg-white hover:text-slate-900'
                    "
                >
                    <i
                        class="fa-regular fa-clock w-5 text-center"
                        :class="
                            isActive('schedule.news')
                                ? 'text-green-600'
                                : 'text-slate-300'
                        "
                    ></i>
                    Schedule News
                </Link>

                <Link
                    :href="route('draft.news')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors duration-200"
                    :class="
                        isActive('draft.news')
                            ? 'bg-green-50 text-green-700'
                            : 'text-slate-100 hover:bg-white hover:text-slate-900'
                    "
                >
                    <i
                        class="fa-solid fa-file-pen w-5 text-center"
                        :class="
                            isActive('draft.news')
                                ? 'text-green-600'
                                : 'text-slate-300'
                        "
                    ></i>
                    Draft News
                </Link>

                <div class="border-t border-slate-100 my-2 pt-2"></div>

                <Link
                    :href="route('id.card')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors duration-200"
                    :class="
                        isActive('id.card')
                            ? 'bg-green-50 text-green-700'
                            : 'text-slate-100 hover:bg-white hover:text-slate-900'
                    "
                >
                    <i
                        class="fa-regular fa-id-badge w-5 text-center"
                        :class="
                            isActive('id.card')
                                ? 'text-green-600'
                                : 'text-slate-300'
                        "
                    ></i>
                    Id Card
                </Link>

                <Link
                    :href="route('reporter.application')"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors duration-200"
                    :class="
                        isActive('reporter.application')
                            ? 'bg-green-50 text-green-700'
                            : 'text-slate-100 hover:bg-white hover:text-slate-900'
                    "
                >
                    <i
                        class="fa-solid fa-address-book w-5 text-center"
                        :class="
                            isActive('reporter.application')
                                ? 'text-green-600'
                                : 'text-slate-300'
                        "
                    ></i>
                    Application Form
                </Link>
            </div>
        </aside>

        <!-- ==================== MAIN CONTENT ==================== -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <!-- Top Header -->
            <header
                class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30"
            >
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Toggle -->
                    <button
                        @click="showAsideMobile = true"
                        class="lg:hidden text-slate-500 hover:text-slate-800 p-2 rounded-lg hover:bg-slate-100 transition-colors"
                    >
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>

                    <!-- Desktop Sidebar Toggle (Optional) -->
                    <button
                        @click="showAsideDesktop = !showAsideDesktop"
                        class="hidden lg:block text-slate-400 hover:text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors"
                    >
                        <i class="fa-solid fa-bars-staggered text-lg"></i>
                    </button>
                </div>

                <div class="flex items-center gap-3 sm:gap-6">
                    <!-- View Site -->
                    <a
                        :href="route('home')"
                        target="_blank"
                        class="hidden sm:flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-green-600 transition-colors"
                    >
                        <i class="fa-regular fa-eye"></i>
                        <span>View Site</span>
                    </a>

                    <!-- User Profile -->
                    <div
                        class="flex items-center gap-3 border-l border-slate-200 pl-3 sm:pl-6"
                    >
                        <Link
                            :href="route('reporter.edit', user.id)"
                            class="flex items-center gap-2 group"
                        >
                            <img
                                :src="
                                    page.props.auth?.user.image
                                        ? '/storage/' +
                                          page.props.auth.user.image
                                        : '/storage/images/user.png'
                                "
                                alt="user image"
                                class="h-9 w-9 rounded-full object-cover ring-2 ring-transparent group-hover:ring-green-500 transition-all"
                            />
                            <div class="hidden sm:block text-left">
                                <span
                                    class="block text-sm font-bold text-slate-700 group-hover:text-green-600 transition-colors leading-tight"
                                >
                                    {{ user.name }}
                                </span>
                                <span
                                    class="block text-[10px] text-slate-400 font-medium"
                                    >Reporter</span
                                >
                            </div>
                        </Link>

                        <!-- Logout -->
                        <form @submit.prevent="logout" class="ml-2">
                            <button
                                type="submit"
                                class="text-slate-400 hover:text-red-500 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                title="Logout"
                            >
                                <i
                                    class="fa-solid fa-arrow-right-from-bracket text-lg"
                                ></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main
                class="flex-1 p-4 sm:p-6 bg-slate-50/50 w-full overflow-x-hidden"
            >
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
aside {
    background-color: #008211;
}
/* Custom Scrollbar for Sidebar */
.custom-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 999px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
