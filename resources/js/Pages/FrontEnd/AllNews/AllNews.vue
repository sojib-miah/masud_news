<script setup>
import Layout from "@/Components/Layout.vue";
import NewsBread from "./NewsBread.vue";
import { usePage, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import { route } from "ziggy-js";

const page = usePage();

const category = computed(() => page.props.category ?? null);
const subcategory = computed(() => page.props.subcategory ?? null);
const news = computed(() => page.props.news ?? []);

// ---------- Sidebar ----------
const latestNews = computed(() => page.props.sidebarLatest ?? []);
const popularNews = computed(() => page.props.sidebarPopular ?? []);
const activeFilter = ref("latest");

const filteredNews = computed(() =>
    activeFilter.value === "latest" ? latestNews.value : popularNews.value,
);

function setFilter(filter) {
    activeFilter.value = filter;
}

// ---------- Safe helpers ----------
function imgUrl(path, fallback = "/images/default.jpg") {
    if (!path || !String(path).trim()) return fallback;
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}

function stripHtml(html) {
    if (!html) return "";
    return String(html).replace(/<[^>]*>/g, "");
}

function descCut(html, len = 200) {
    const text = stripHtml(html).trim();
    return text.length > len ? text.slice(0, len) + " ..." : text;
}

function truncate(text, words = 8) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}

// Featured and others
const featured = computed(() => news.value[0] ?? null);
const secondary = computed(() => news.value.slice(1, 4));
const gridNews = computed(() => news.value.slice(4, 13));
</script>

<template>
    <Layout>
        <NewsBread :category="category" :subcategory="subcategory" />
        <div class="bg-[#f8fafc] py-6 sm:py-8">
            <div class="container mx-auto px-3 sm:px-4 lg:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- ==================================================
                     MAIN NEWS (2/3)
                =================================================== -->
                    <main class="lg:col-span-2 space-y-6">
                        <template v-if="news.length">
                            <!-- ==================== FEATURED ==================== -->
                            <article v-if="featured" class="featured-card">
                                <Link
                                    :href="route('news.details', featured.slug)"
                                    class="featured-img-wrap"
                                >
                                    <img
                                        class="featured-img"
                                        :src="imgUrl(featured.image)"
                                        :alt="featured.title"
                                        loading="lazy"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                    />
                                    <span class="featured-overlay"></span>
                                    <span class="featured-badge">
                                        <i class="fa-solid fa-star"></i>
                                        প্রধান সংবাদ
                                    </span>
                                </Link>

                                <div class="featured-body">
                                    <Link
                                        :href="
                                            route('news.details', featured.slug)
                                        "
                                        class="featured-title"
                                    >
                                        {{ featured.title }}
                                    </Link>

                                    <p class="featured-desc">
                                        {{ descCut(featured.description, 240) }}
                                    </p>

                                    <Link
                                        :href="
                                            route('news.details', featured.slug)
                                        "
                                        class="read-more"
                                    >
                                        আরও পড়ুন
                                        <i
                                            class="fa-solid fa-angle-right text-[0.7rem]"
                                        ></i>
                                    </Link>
                                </div>
                            </article>

                            <!-- ==================== SECONDARY ==================== -->
                            <section
                                v-if="secondary.length"
                                class="secondary-grid"
                            >
                                <article
                                    v-for="item in secondary"
                                    :key="item.id"
                                    class="secondary-card"
                                >
                                    <Link
                                        :href="route('news.details', item.slug)"
                                        class="secondary-img-wrap"
                                    >
                                        <img
                                            class="secondary-img"
                                            :src="imgUrl(item.image)"
                                            :alt="item.title"
                                            loading="lazy"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                    </Link>

                                    <Link
                                        :href="route('news.details', item.slug)"
                                        class="secondary-title"
                                    >
                                        {{ truncate(item.title, 9) }}
                                    </Link>
                                </article>
                            </section>

                            <!-- ==================== GRID ==================== -->
                            <section v-if="gridNews.length" class="news-grid">
                                <article
                                    v-for="item in gridNews"
                                    :key="item.id"
                                    class="grid-card"
                                >
                                    <Link
                                        :href="route('news.details', item.slug)"
                                        class="grid-img-wrap"
                                    >
                                        <img
                                            class="grid-img"
                                            :src="imgUrl(item.image)"
                                            :alt="item.title"
                                            loading="lazy"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                    </Link>

                                    <Link
                                        :href="route('news.details', item.slug)"
                                        class="grid-title"
                                    >
                                        {{ truncate(item.title, 10) }}
                                    </Link>
                                </article>
                            </section>
                        </template>

                        <!-- ==================== EMPTY ==================== -->
                        <div v-else class="empty-state">
                            <i class="fa-regular fa-newspaper"></i>
                            <p>এই ক্যাটাগরিতে কোনো সংবাদ পাওয়া যায়নি।</p>
                        </div>
                    </main>

                    <!-- ==================================================
                     SIDEBAR (1/3)
                =================================================== -->
                    <aside class="lg:col-span-1">
                        <div class="sidebar-card">
                            <!-- Tabs -->
                            <div class="sidebar-tabs" role="tablist">
                                <button
                                    class="sidebar-tab"
                                    :class="{
                                        active: activeFilter === 'latest',
                                    }"
                                    role="tab"
                                    :aria-selected="activeFilter === 'latest'"
                                    @click="setFilter('latest')"
                                >
                                    <i class="fa-regular fa-clock"></i>
                                    সর্বশেষ
                                </button>
                                <button
                                    class="sidebar-tab"
                                    :class="{
                                        active: activeFilter === 'popular',
                                    }"
                                    role="tab"
                                    :aria-selected="activeFilter === 'popular'"
                                    @click="setFilter('popular')"
                                >
                                    <i class="fa-solid fa-fire"></i>
                                    জনপ্রিয়
                                </button>
                            </div>

                            <!-- List -->
                            <div class="sidebar-list">
                                <Link
                                    v-for="(item, i) in filteredNews.slice(
                                        0,
                                        10,
                                    )"
                                    :key="item.id"
                                    :href="route('news.details', item.slug)"
                                    class="sidebar-item"
                                >
                                    <span
                                        class="sidebar-rank"
                                        :class="
                                            i === 0
                                                ? 'gold'
                                                : i === 1
                                                  ? 'silver'
                                                  : i === 2
                                                    ? 'bronze'
                                                    : ''
                                        "
                                    >
                                        {{ i + 1 }}
                                    </span>

                                    <div class="sidebar-thumb-wrap">
                                        <img
                                            class="sidebar-thumb"
                                            :src="imgUrl(item.image)"
                                            :alt="item.title"
                                            loading="lazy"
                                            @error="
                                                (e) =>
                                                    (e.target.src =
                                                        '/images/default.jpg')
                                            "
                                        />
                                    </div>

                                    <p class="sidebar-title">
                                        {{ truncate(item.title, 9) }}
                                    </p>
                                </Link>

                                <!-- Empty -->
                                <div
                                    v-if="filteredNews.length === 0"
                                    class="sidebar-empty"
                                >
                                    <i class="fa-regular fa-newspaper"></i>
                                    <p>কোনো সংবাদ পাওয়া যায়নি।</p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
aside {
    background-color: transparent;
}
/* ==================== FEATURED CARD ==================== */
.featured-card {
    /* background: #f8fafc; */
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    transition:
        box-shadow 0.3s ease,
        transform 0.3s ease;
}

.featured-card:hover {
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 16px 36px rgba(0, 147, 12, 0.1);
    transform: translateY(-2px);
}

.featured-img-wrap {
    display: block;
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: #e2e8f0;
    isolation: isolate;
}

.featured-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
}

.featured-img-wrap:hover .featured-img {
    transform: scale(1.05);
}

.featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 55%,
        rgba(0, 0, 0, 0.35) 100%
    );
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.featured-img-wrap:hover .featured-overlay {
    opacity: 1;
}

.featured-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    border-radius: 999px;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.35);
    z-index: 2;
}

.featured-badge i {
    font-size: 0.65rem;
}

.featured-body {
    padding: 16px 18px 18px;
}

.featured-title {
    display: block;
    color: #0f172a;
    font-size: 1.15rem;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 8px;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.featured-title:hover {
    color: #00930c;
}

@media (min-width: 640px) {
    .featured-title {
        font-size: 1.35rem;
    }
}

@media (min-width: 1024px) {
    .featured-title {
        font-size: 1.5rem;
        -webkit-line-clamp: 2;
    }
}

.featured-desc {
    color: #475569;
    font-size: 0.88rem;
    line-height: 1.65;
    text-align: justify;
    margin: 0 0 12px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (min-width: 640px) {
    .featured-desc {
        font-size: 0.92rem;
        -webkit-line-clamp: 3;
    }
}

.read-more {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #00930c;
    font-size: 0.86rem;
    font-weight: 600;
    transition:
        gap 0.2s,
        color 0.2s;
    text-decoration: none;
}

.read-more:hover {
    color: #006b09;
    gap: 7px;
}

/* ==================== SECONDARY GRID ==================== */
.secondary-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 14px;
}

@media (min-width: 480px) {
    .secondary-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.secondary-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 4px 12px rgba(0, 0, 0, 0.04);
    transition:
        box-shadow 0.25s ease,
        transform 0.25s ease;
}

.secondary-card:hover {
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 12px 28px rgba(0, 147, 12, 0.1);
    transform: translateY(-3px);
}

.secondary-img-wrap {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
}

.secondary-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.secondary-card:hover .secondary-img {
    transform: scale(1.06);
}

.secondary-title {
    display: block;
    padding: 10px 12px 12px;
    color: #1e293b;
    font-size: 0.85rem;
    font-weight: 600;
    line-height: 1.45;
    text-decoration: none;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.secondary-title:hover {
    color: #00930c;
}

@media (min-width: 640px) {
    .secondary-title {
        font-size: 0.9rem;
        padding: 12px 14px 14px;
    }
}

/* ==================== GRID CARD ==================== */
.news-grid {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    gap: 14px;
}

@media (min-width: 480px) {
    .news-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .news-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.grid-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 4px 12px rgba(0, 0, 0, 0.04);
    transition:
        box-shadow 0.25s ease,
        transform 0.25s ease;
}

.grid-card:hover {
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 12px 28px rgba(0, 147, 12, 0.1);
    transform: translateY(-3px);
}

.grid-img-wrap {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
}

.grid-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.grid-card:hover .grid-img {
    transform: scale(1.06);
}

.grid-title {
    display: block;
    padding: 10px 12px 12px;
    color: #1e293b;
    font-size: 0.85rem;
    font-weight: 500;
    line-height: 1.45;
    text-decoration: none;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.grid-title:hover {
    color: #00930c;
}

@media (min-width: 640px) {
    .grid-title {
        font-size: 0.88rem;
        padding: 12px 14px 14px;
    }
}

/* ==================== EMPTY STATE ==================== */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 60px 20px;
    background: #ffffff;
    border-radius: 14px;
    color: #94a3b8;
    border: 1px dashed #e2e8f0;
    text-align: center;
}

.empty-state i {
    font-size: 36px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 0.9rem;
    margin: 0;
}

/* ==================== SIDEBAR ==================== */
.sidebar-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 50px;
}

.sidebar-tabs {
    display: flex;
    gap: 6px;
    padding: 8px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.sidebar-tab {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 12px;
    background: transparent;
    color: #64748b;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s,
        box-shadow 0.2s;
    font-family: inherit;
}

.sidebar-tab:hover {
    color: #0f172a;
    background: #eef2f6;
}

.sidebar-tab.active {
    background: linear-gradient(135deg, #00930c 0%, #00b515 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.28);
}

.sidebar-tab i {
    font-size: 0.85em;
}

/* List */
.sidebar-list {
    padding: 8px;
    max-height: 600px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.sidebar-list::-webkit-scrollbar {
    width: 6px;
}

.sidebar-list::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.sidebar-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.sidebar-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 8px;
    text-decoration: none;
    border-radius: 8px;
    border-bottom: 1px dashed #e2e8f0;
    transition: background 0.15s;
}

.sidebar-item:last-child {
    border-bottom: none;
}

.sidebar-item:hover {
    background: #f0fdf4;
}

.sidebar-item:hover .sidebar-title {
    color: #00930c;
}

.sidebar-rank {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.sidebar-rank.gold {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #fff;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
}

.sidebar-rank.silver {
    background: linear-gradient(135deg, #cbd5e1, #94a3b8);
    color: #fff;
}

.sidebar-rank.bronze {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
}

.sidebar-thumb-wrap {
    flex-shrink: 0;
    width: 74px;
    height: 54px;
    border-radius: 6px;
    overflow: hidden;
    background: #e2e8f0;
}

.sidebar-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.sidebar-item:hover .sidebar-thumb {
    transform: scale(1.08);
}

.sidebar-title {
    flex: 1;
    color: #1e293b;
    font-size: 0.84rem;
    font-weight: 500;
    line-height: 1.4;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

@media (min-width: 640px) {
    .sidebar-title {
        font-size: 0.87rem;
    }
    .sidebar-thumb-wrap {
        width: 82px;
        height: 60px;
    }
}

/* Empty */
.sidebar-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
    color: #94a3b8;
    gap: 8px;
    text-align: center;
}

.sidebar-empty i {
    font-size: 28px;
    opacity: 0.5;
}

.sidebar-empty p {
    font-size: 0.85rem;
    margin: 0;
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 360px) {
    .featured-title {
        font-size: 1.02rem;
    }
    .featured-desc {
        font-size: 0.82rem;
    }
    .secondary-title,
    .grid-title {
        font-size: 0.8rem;
        padding: 8px 10px 10px;
    }
    .sidebar-title {
        font-size: 0.78rem;
    }
    .sidebar-thumb-wrap {
        width: 62px;
        height: 48px;
    }
    .sidebar-rank {
        width: 22px;
        height: 22px;
        font-size: 0.72rem;
    }
    .sidebar-tab {
        font-size: 0.78rem;
        padding: 8px 6px;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .featured-title {
        font-size: 1.6rem;
    }
    .featured-desc {
        font-size: 0.96rem;
    }
    .secondary-title,
    .grid-title {
        font-size: 0.93rem;
        padding: 14px 16px 16px;
    }
    .sidebar-title {
        font-size: 0.9rem;
    }
    .sidebar-thumb-wrap {
        width: 92px;
        height: 66px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .featured-card,
    .secondary-card,
    .grid-card,
    .featured-img,
    .secondary-img,
    .grid-img,
    .sidebar-thumb,
    .read-more {
        transition: none;
    }
    .featured-card:hover,
    .secondary-card:hover,
    .grid-card:hover {
        transform: none;
    }
    .featured-img-wrap:hover .featured-img,
    .secondary-card:hover .secondary-img,
    .grid-card:hover .grid-img,
    .sidebar-item:hover .sidebar-thumb {
        transform: none;
    }
}
</style>
