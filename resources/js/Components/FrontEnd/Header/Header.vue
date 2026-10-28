<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from "vue";

const page = usePage();
const categories = page.props.categories;

// ---------- Popup (Search) ----------
const showPopup = ref(false);
function openPopup() {
    showPopup.value = true;
}
function closePopup() {
    showPopup.value = false;
}
function handleClickOutside(e) {
    if (showPopup.value && e.target.classList.contains("popup-overlay")) {
        closePopup();
    }
}
onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener("click", handleClickOutside),
);

// ---------- Mobile menu ----------
const mobileMenu = ref(false);
const openSub = ref(null);
const toggleSub = (id) => {
    openSub.value = openSub.value === id ? null : id;
};

// ---------- Horizontal scroll for desktop menu ----------
const menuScroll = ref(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

function updateArrows() {
    const el = menuScroll.value;
    if (!el) return;
    canScrollLeft.value = el.scrollLeft > 5;
    canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 5;
}

function scrollBy(direction) {
    const el = menuScroll.value;
    if (!el) return;
    const amount = Math.max(el.clientWidth * 0.6, 200);
    el.scrollBy({ left: direction * amount, behavior: "smooth" });
}

let resizeObserver = null;

onMounted(async () => {
    await nextTick();
    updateArrows();
    if (menuScroll.value) {
        resizeObserver = new ResizeObserver(updateArrows);
        resizeObserver.observe(menuScroll.value);
    }
    window.addEventListener("resize", updateArrows);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", updateArrows);
    if (resizeObserver) resizeObserver.disconnect();
});

watch(categories, async () => {
    await nextTick();
    updateArrows();
});

// ---------- Hover dropdown (teleported) ----------
const hoverMenu = ref(null); // { cat, x, y }
let hoverTimer = null;

function showDropdown(cat, event) {
    if (!cat.sub_categories?.length) return;
    if (hoverTimer) clearTimeout(hoverTimer);
    const rect = event.currentTarget.getBoundingClientRect();
    hoverMenu.value = {
        cat,
        x: rect.left,
        y: rect.bottom,
    };
}

function hideDropdown() {
    hoverTimer = setTimeout(() => {
        hoverMenu.value = null;
    }, 120);
}

function cancelHide() {
    if (hoverTimer) clearTimeout(hoverTimer);
}
</script>

<template>
    <div class="header">
        <div class="container mx-auto px-3">
            <div class="flex justify-between items-center gap-2">
                <!-- Mobile hamburger -->
                <button
                    class="lg:hidden text-2xl p-2 rounded hover:bg-white/10 transition"
                    @click="mobileMenu = !mobileMenu"
                    aria-label="Toggle menu"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- ============ DESKTOP: scrollable menu with arrows ============ -->
                <div class="hidden lg:flex items-center flex-1 min-w-0">
                    <button
                        v-show="canScrollLeft"
                        class="scroll-arrow"
                        @click="scrollBy(-1)"
                        aria-label="Scroll left"
                    >
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <ul
                        ref="menuScroll"
                        class="desktop-menu flex-1 min-w-0"
                        @scroll="updateArrows"
                    >
                        <li>
                            <Link :href="route('home')" class="active">
                                <i class="fa-regular fa-house"></i>
                            </Link>
                        </li>

                        <li
                            v-for="cat in categories"
                            :key="cat.id"
                            class="shrink-0"
                            @mouseenter="showDropdown(cat, $event)"
                            @mouseleave="hideDropdown"
                        >
                            <Link
                                :href="route('all.news', [cat.slug])"
                                class="flex items-center gap-1 whitespace-nowrap"
                            >
                                {{ cat.name }}
                                <i
                                    v-if="cat.sub_categories?.length"
                                    class="fa-solid fa-angle-down text-[0.7rem]"
                                ></i>
                            </Link>
                        </li>

                        <li class="shrink-0">
                            <Link :href="route('news.archive')">
                                <i class="fa-solid fa-box-archive"></i>
                            </Link>
                        </li>
                    </ul>

                    <button
                        v-show="canScrollRight"
                        class="scroll-arrow"
                        @click="scrollBy(1)"
                        aria-label="Scroll right"
                    >
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <!-- ============ MOBILE: accordion menu ============ -->
                <ul
                    v-if="mobileMenu"
                    class="mobile-menu lg:hidden absolute top-full left-0 right-0 z-40"
                >
                    <li>
                        <Link :href="route('home')" class="active">
                            <i class="fa-regular fa-house"></i>
                            <span class="ml-2">হোম</span>
                        </Link>
                    </li>

                    <li v-for="cat in categories" :key="cat.id">
                        <div
                            class="flex items-center justify-between px-3 py-3 border-b border-white/15 cursor-pointer"
                            @click="toggleSub(cat.id)"
                        >
                            <Link
                                :href="route('all.news', [cat.slug])"
                                class="flex-1 text-white"
                                @click.stop
                            >
                                {{ cat.name }}
                            </Link>
                            <i
                                v-if="cat.sub_categories?.length"
                                :class="[
                                    'fa-solid fa-angle-down transition-transform',
                                    openSub === cat.id ? 'rotate-180' : '',
                                ]"
                            ></i>
                        </div>

                        <ul
                            v-if="
                                cat.sub_categories?.length && openSub === cat.id
                            "
                            class="submenu-mobile"
                        >
                            <li v-for="sub in cat.sub_categories" :key="sub.id">
                                <Link
                                    :href="
                                        route('all.news', [cat.slug, sub.slug])
                                    "
                                >
                                    {{ sub.name }}
                                </Link>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <Link :href="route('news.archive')">
                            <i class="fa-solid fa-box-archive"></i>
                            <span class="ml-2">আর্কাইভ</span>
                        </Link>
                    </li>
                </ul>

                <!-- Search button -->
                <button
                    type="button"
                    class="text-xl p-2 rounded hover:bg-white/10 transition"
                    @click="openPopup"
                    aria-label="Search"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <!-- Popup -->
                <div
                    v-if="showPopup"
                    class="popup-overlay fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="relative bg-white rounded-lg shadow-xl p-4 sm:p-6 w-full max-w-[600px]"
                    >
                        <button
                            @click="closePopup"
                            class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-xl leading-none p-1"
                            aria-label="Close"
                        >
                            ✕
                        </button>

                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            খুঁজুন
                        </h3>

                        <input
                            type="search"
                            placeholder="Search news..."
                            class="form-input w-full mb-4 border rounded px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none"
                        />

                        <button
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded transition"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ TELEPORTED DESKTOP DROPDOWN ============ -->
        <Teleport to="body">
            <div
                v-if="hoverMenu"
                class="desktop-dropdown"
                :style="{
                    left: hoverMenu.x + 'px',
                    top: hoverMenu.y + 'px',
                }"
                @mouseenter="cancelHide"
                @mouseleave="hideDropdown"
            >
                <ul>
                    <li
                        v-for="sub in hoverMenu.cat.sub_categories"
                        :key="sub.id"
                    >
                        <Link
                            :href="
                                route('all.news', [
                                    hoverMenu.cat.slug,
                                    sub.slug,
                                ])
                            "
                        >
                            {{ sub.name }}
                        </Link>
                    </li>
                </ul>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
ul {
    list-style: none;
    margin: 0;
    padding: 0;
}
ul li a {
    color: #fff;
}

.header {
    position: relative;
    background: linear-gradient(90deg, #00a815, #00930c, #00a815);
    background-size: 200% 200%;
    color: #fff;
    animation: gradientMove 6s ease infinite;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

/* ---------- Desktop scrollable menu ---------- */
.desktop-menu {
    display: flex;
    flex-direction: row;
    align-items: center;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    -ms-overflow-style: none;
    scrollbar-width: none;
    padding: 4px 0;
}

.desktop-menu::-webkit-scrollbar {
    display: none;
}

.desktop-menu > li {
    border-right: 1px solid rgba(255, 255, 255, 0.25);
}

.desktop-menu > li:last-child {
    border-right: none;
}

.desktop-menu li a {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 14px;
    color: #fff;
    white-space: nowrap;
    transition: background 0.2s ease;
}

.desktop-menu li a:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* ---------- Scroll arrows ---------- */
.scroll-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.35);
    transition:
        background 0.2s,
        transform 0.15s;
    backdrop-filter: blur(4px);
}

.scroll-arrow:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: scale(1.08);
}

.scroll-arrow:active {
    transform: scale(0.95);
}

/* ---------- Mobile menu ---------- */
.mobile-menu {
    background: #2d8a34;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    max-height: 75vh;
    overflow-y: auto;
    animation: slideDown 0.2s ease;
}

.mobile-menu > li > a {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    color: #fff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    transition: background 0.2s;
}

.mobile-menu > li > a:hover {
    background: rgba(255, 255, 255, 0.1);
}

.submenu-mobile {
    background: #1f5a22;
    padding-left: 12px;
    animation: slideDown 0.2s ease;
}

.submenu-mobile li a {
    display: block;
    padding: 10px 16px;
    color: #fff;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.95rem;
}

.submenu-mobile li a:hover {
    background: rgba(255, 255, 255, 0.1);
}

/* ---------- Animations ---------- */
@keyframes gradientMove {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- NOT scoped: the teleported dropdown lives outside this component tree -->
<style>
.desktop-dropdown {
    position: fixed;
    min-width: 200px;
    background: #1f5a22;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    padding: 4px 0;
    animation: dropdownFade 0.15s ease;
}

.desktop-dropdown ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.desktop-dropdown li a {
    display: block;
    padding: 10px 18px;
    color: #fff;
    white-space: nowrap;
    transition: background 0.15s;
}

.desktop-dropdown li a:hover {
    background: #2d8a34;
}

@keyframes dropdownFade {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
