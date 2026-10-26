<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    video: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

// Safe data sources
const latestNews = computed(() => page.props.sidebarLatest ?? []);
const popularNews = computed(() => page.props.sidebarPopular ?? []);

const activeFilter = ref("latest");

const filteredNews = computed(() =>
    activeFilter.value === "latest" ? latestNews.value : popularNews.value,
);

function setFilter(filter) {
    activeFilter.value = filter;
}

// Reset to valid index if video list becomes empty
const currentVideo = computed(() => props.video?.[0] ?? null);

// YouTube ID extractor — handles more URL shapes
function extractYoutubeId(url) {
    if (!url) return null;
    const regExp =
        /(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/;
    const match = String(url).match(regExp);
    return match ? match[1] : null;
}

const videoEmbedUrl = computed(() => {
    const id = extractYoutubeId(currentVideo.value?.link);
    return id ? `https://www.youtube.com/embed/${id}` : null;
});

// Safe image fallback
function imgUrl(path) {
    if (!path || !String(path).trim()) return "/images/default.jpg";
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}

// Title truncation
function truncate(text, words = 9) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}
</script>

<template>
    <section class="vns-section">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
                <!-- ============ VIDEO (2/3 on desktop) ============ -->
                <div class="lg:col-span-2">
                    <div class="video-card">
                        <div class="video-badge">
                            <span class="live-dot"></span>
                            ভিডিও নিউজ
                        </div>

                        <div class="video-frame-wrap">
                            <iframe
                                v-if="videoEmbedUrl"
                                class="video-frame"
                                :src="videoEmbedUrl"
                                :title="currentVideo?.title || 'YouTube video'"
                                frameborder="0"
                                allow="
                                    accelerometer;
                                    autoplay;
                                    clipboard-write;
                                    encrypted-media;
                                    gyroscope;
                                    picture-in-picture;
                                    web-share;
                                "
                                allowfullscreen
                                loading="lazy"
                            ></iframe>

                            <!-- Fallback when no video -->
                            <div v-else class="video-fallback">
                                <i class="fa-solid fa-play"></i>
                                <p>কোনো ভিডিও নেই</p>
                            </div>
                        </div>

                        <!-- Title below video -->
                        <div v-if="currentVideo?.title" class="video-caption">
                            <i class="fa-solid fa-circle-play"></i>
                            <span>{{ currentVideo.title }}</span>
                        </div>
                    </div>
                </div>

                <!-- ============ NEWS SIDEBAR (1/3) ============ -->
                <aside class="lg:col-span-1">
                    <div class="sidebar-card">
                        <!-- Tabs -->
                        <div class="tabs" role="tablist">
                            <button
                                class="tab"
                                :class="{
                                    active: activeFilter === 'latest',
                                }"
                                role="tab"
                                :aria-selected="activeFilter === 'latest'"
                                @click="setFilter('latest')"
                            >
                                <i class="fa-regular fa-clock tab-ico"></i>
                                সর্বশেষ
                            </button>

                            <button
                                class="tab"
                                :class="{
                                    active: activeFilter === 'popular',
                                }"
                                role="tab"
                                :aria-selected="activeFilter === 'popular'"
                                @click="setFilter('popular')"
                            >
                                <i class="fa-solid fa-fire tab-ico"></i>
                                জনপ্রিয়
                            </button>
                        </div>

                        <!-- News list -->
                        <div class="news-box">
                            <transition name="fade-list" mode="out-in">
                                <div :key="activeFilter" class="news-scroll">
                                    <Link
                                        v-for="(item, i) in filteredNews.slice(
                                            0,
                                            10,
                                        )"
                                        :key="item.id"
                                        :href="route('news.details', item.slug)"
                                        class="news-row"
                                    >
                                        <!-- Rank badge -->
                                        <span
                                            class="news-rank"
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

                                        <!-- Thumbnail -->
                                        <div class="news-thumb-wrap">
                                            <img
                                                class="news-thumb"
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

                                        <!-- Title -->
                                        <p class="news-title">
                                            {{ truncate(item.title, 9) }}
                                        </p>
                                    </Link>

                                    <div
                                        v-if="filteredNews.length === 0"
                                        class="empty-state"
                                    >
                                        <i
                                            class="fa-regular fa-newspaper empty-ico"
                                        ></i>
                                        <p>কোনো সংবাদ পাওয়া যায়নি।</p>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* ==================== SECTION ==================== */
.vns-section {
    background: #f1f5f9;
    padding: 24px 0;
}

/* ==================== VIDEO CARD ==================== */
.video-card {
    position: relative;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
}

.video-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    border-radius: 999px;
    backdrop-filter: blur(6px);
}

.live-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulseDot 1.6s infinite;
}

@keyframes pulseDot {
    0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}

.video-frame-wrap {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* 16:9 */
    background: #000;
}

.video-frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.video-fallback {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #94a3b8;
    background: linear-gradient(135deg, #1e293b, #0f172a);
}

.video-fallback i {
    font-size: 44px;
    color: #475569;
}

.video-fallback p {
    font-size: 0.9rem;
}

.video-caption {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #0f172a;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    line-height: 1.4;
}

.video-caption i {
    color: #00930c;
    flex-shrink: 0;
}

/* ==================== SIDEBAR CARD ==================== */
.sidebar-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* ==================== TABS ==================== */
.tabs {
    display: flex;
    gap: 6px;
    padding: 8px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.tab {
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

.tab:hover {
    color: #0f172a;
    background: #eef2f6;
}

.tab.active {
    background: linear-gradient(135deg, #00930c 0%, #00b515 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.28);
}

.tab-ico {
    font-size: 0.85em;
}

/* ==================== NEWS BOX ==================== */
.news-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.news-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
    max-height: 420px;

    /* Custom scrollbar */
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.news-scroll::-webkit-scrollbar {
    width: 6px;
}

.news-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.news-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.news-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* ==================== NEWS ROW ==================== */
.news-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 8px;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
    border-bottom: 1px dashed #e2e8f0;
}

.news-row:last-child {
    border-bottom: none;
}

.news-row:hover {
    background: #f0fdf4;
}

.news-row:hover .news-title {
    color: #00930c;
}

/* Rank badge */
.news-rank {
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

.news-rank.gold {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #fff;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
}

.news-rank.silver {
    background: linear-gradient(135deg, #cbd5e1, #94a3b8);
    color: #fff;
}

.news-rank.bronze {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
}

/* Thumb */
.news-thumb-wrap {
    flex-shrink: 0;
    width: 68px;
    height: 50px;
    border-radius: 6px;
    overflow: hidden;
    background: #e2e8f0;
}

.news-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.news-row:hover .news-thumb {
    transform: scale(1.08);
}

/* Title */
.news-title {
    flex: 1;
    color: #1e293b;
    font-size: 0.85rem;
    font-weight: 500;
    line-height: 1.5;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

/* ==================== EMPTY ==================== */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
    color: #94a3b8;
    text-align: center;
    gap: 10px;
}

.empty-ico {
    font-size: 34px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 0.85rem;
    margin: 0;
}

/* ==================== FADE TRANSITION (list swap) ==================== */
.fade-list-enter-active,
.fade-list-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.fade-list-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.fade-list-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ==================== RESPONSIVE ==================== */
/* Tablet: video spans full, sidebar below in 2 cols */
@media (min-width: 640px) and (max-width: 1023px) {
    .news-scroll {
        max-height: 360px;
    }

    .news-thumb-wrap {
        width: 80px;
        height: 58px;
    }

    .news-title {
        font-size: 0.9rem;
    }
}

/* Desktop & up: bigger thumbs and titles */
@media (min-width: 1024px) {
    .news-thumb-wrap {
        width: 82px;
        height: 60px;
    }

    .news-title {
        font-size: 0.88rem;
    }

    .news-scroll {
        /* max-height: 480px; */
        max-height: 295px;
    }
}

/* Large desktop: roomier */
@media (min-width: 1280px) {
    .news-scroll {
        /* max-height: 520px; */
        max-height: 410px;
    }

    .news-thumb-wrap {
        width: 92px;
        height: 66px;
    }

    .news-title {
        font-size: 0.92rem;
        -webkit-line-clamp: 2;
    }

    .video-caption {
        font-size: 0.95rem;
    }
}

/* Very small phones */
@media (max-width: 400px) {
    .tab {
        font-size: 0.78rem;
        padding: 8px 6px;
        gap: 4px;
    }

    .news-thumb-wrap {
        width: 58px;
        height: 44px;
    }

    .news-title {
        font-size: 0.8rem;
    }

    .news-rank {
        width: 22px;
        height: 22px;
        font-size: 0.7rem;
    }

    .video-badge {
        font-size: 0.68rem;
        padding: 5px 10px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .live-dot {
        animation: none;
    }
    .news-thumb,
    .news-row {
        transition: none;
    }
    .fade-list-enter-active,
    .fade-list-leave-active {
        transition: opacity 0.15s;
    }
}
</style>
