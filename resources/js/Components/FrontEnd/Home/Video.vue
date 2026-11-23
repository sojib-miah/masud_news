<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    video: { type: Array, default: () => [] },
});

const page = usePage();

// ── Data sources ──
const latestNews = computed(() => page.props.sidebarLatest ?? []);
const popularNews = computed(() => page.props.sidebarPopular ?? []);

const activeFilter = ref("latest");
const filteredNews = computed(() =>
    activeFilter.value === "latest" ? latestNews.value : popularNews.value,
);
function setFilter(filter) {
    activeFilter.value = filter;
}

// ── Video ──
const currentVideo = computed(() => props.video?.[0] ?? null);

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

const youtubeThumbnail = computed(() => {
    const id = extractYoutubeId(currentVideo.value?.link);
    return id
        ? `https://i.ytimg.com/vi/${id}/hqdefault.jpg`
        : "/images/default.jpg";
});

const isVideoLoaded = ref(false);
watch(currentVideo, () => (isVideoLoaded.value = false));

// ── Helpers ──
function imgUrl(path) {
    if (!path || !String(path).trim()) return "/images/default.jpg";
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}
function truncate(text, words = 9) {
    if (!text) return "";
    const parts = String(text).split(/\s+/);
    return parts.length > words ? parts.slice(0, words).join(" ") + "…" : text;
}
function onImgError(e) {
    e.target.src = "/images/default.jpg";
}
</script>

<template>
    <section class="vns-section">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div
                class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-5 lg:gap-6"
            >
                <!-- ============================================================
                     VIDEO — spans 2 cols on lg, full width below
                     ============================================================ -->
                <div class="lg:col-span-2">
                    <article class="video-card">
                        <!-- Badge -->
                        <div class="video-badge">
                            <span class="live-dot"></span>
                            ভিডিও নিউজ
                        </div>

                        <!-- Frame / Poster / Fallback -->
                        <div class="video-frame-wrap">
                            <iframe
                                v-if="videoEmbedUrl && isVideoLoaded"
                                class="video-frame"
                                :src="`${videoEmbedUrl}?autoplay=1&rel=0`"
                                :title="currentVideo?.title || 'ভিডিও সংবাদ'"
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

                            <button
                                v-else-if="videoEmbedUrl"
                                type="button"
                                class="video-poster"
                                aria-label="ভিডিও চালু করুন"
                                @click="isVideoLoaded = true"
                            >
                                <img
                                    :src="youtubeThumbnail"
                                    alt=""
                                    width="640"
                                    height="360"
                                    loading="lazy"
                                    @error="onImgError"
                                />
                                <span
                                    class="video-poster-overlay"
                                    aria-hidden="true"
                                ></span>
                                <span
                                    class="video-play-button"
                                    aria-hidden="true"
                                >
                                    <i class="fa-solid fa-play"></i>
                                </span>
                            </button>

                            <div v-else class="video-fallback">
                                <i class="fa-solid fa-clapperboard"></i>
                                <p>কোনো ভিডিও নেই</p>
                            </div>
                        </div>

                        <!-- Caption -->
                        <div v-if="currentVideo?.title" class="video-caption">
                            <i class="fa-solid fa-circle-play"></i>
                            <span>{{ currentVideo.title }}</span>
                        </div>
                    </article>
                </div>

                <!-- ============================================================
                     NEWS SIDEBAR
                     ============================================================ -->
                <aside class="lg:col-span-1">
                    <div class="sidebar-card">
                        <!-- Tabs -->
                        <div class="tabs" role="tablist">
                            <button
                                class="tab"
                                :class="{ active: activeFilter === 'latest' }"
                                role="tab"
                                :aria-selected="activeFilter === 'latest'"
                                @click="setFilter('latest')"
                            >
                                <i class="fa-regular fa-clock tab-ico"></i>
                                <span>সর্বশেষ</span>
                            </button>
                            <button
                                class="tab"
                                :class="{ active: activeFilter === 'popular' }"
                                role="tab"
                                :aria-selected="activeFilter === 'popular'"
                                @click="setFilter('popular')"
                            >
                                <i class="fa-solid fa-fire tab-ico"></i>
                                <span>জনপ্রিয়</span>
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

                                        <div class="news-thumb-wrap">
                                            <img
                                                class="news-thumb"
                                                :src="imgUrl(item.image)"
                                                :alt="item.title"
                                                loading="lazy"
                                                @error="onImgError"
                                            />
                                        </div>

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
aside {
    background: transparent;
}
/* ============================================================
   SECTION
   ============================================================ */
.vns-section {
    background: linear-gradient(180deg, #f1f5f9 0%, #eef2f7 100%);
    padding: 20px 0;
}
@media (min-width: 640px) {
    .vns-section {
        padding: 28px 0;
    }
}
@media (min-width: 1024px) {
    .vns-section {
        padding: 32px 0;
    }
}

/* ============================================================
   VIDEO CARD
   ============================================================ */
.video-card {
    position: relative;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.05),
        0 8px 24px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.video-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 11px;
    background: rgba(0, 0, 0, 0.72);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    border-radius: 999px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
}
@media (min-width: 640px) {
    .video-badge {
        top: 12px;
        left: 12px;
        font-size: 0.75rem;
        padding: 6px 12px;
    }
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

/* -------- Frame container 16:9 -------- */
.video-frame-wrap {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%;
    background: #000;
    overflow: hidden;
    height: stretch;
}

.video-frame {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* -------- Poster (click to load) -------- */
.video-poster {
    position: absolute;
    inset: 0;
    display: block;
    width: 100%;
    height: 100%;
    padding: 0;
    border: 0;
    background: #000;
    cursor: pointer;
    overflow: hidden;
}
.video-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.6s ease;
}
.video-poster:hover img {
    transform: scale(1.04);
}

.video-poster-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        rgba(0, 0, 0, 0.05) 0%,
        rgba(0, 0, 0, 0.45) 100%
    );
    pointer-events: none;
}

.video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e11d48, #be123c);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow:
        0 8px 24px rgba(225, 29, 72, 0.5),
        0 0 0 0 rgba(225, 29, 72, 0.4);
    transition:
        transform 0.2s,
        box-shadow 0.2s;
}
.video-poster:hover .video-play-button {
    transform: translate(-50%, -50%) scale(1.08);
    box-shadow:
        0 12px 32px rgba(225, 29, 72, 0.6),
        0 0 0 12px rgba(225, 29, 72, 0.12);
}
@media (min-width: 640px) {
    .video-play-button {
        width: 72px;
        height: 72px;
        font-size: 26px;
    }
}

/* -------- Fallback -------- */
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

/* -------- Caption -------- */
.video-caption {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    font-size: 0.88rem;
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
@media (min-width: 640px) {
    .video-caption {
        padding: 14px 16px;
        font-size: 0.92rem;
    }
}
@media (min-width: 1280px) {
    .video-caption {
        font-size: 0.98rem;
    }
}

/* ============================================================
   SIDEBAR CARD
   ============================================================ */
.sidebar-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.05),
        0 8px 24px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* -------- Tabs -------- */
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
    padding: 9px 10px;
    background: transparent;
    color: #64748b;
    border: none;
    border-radius: 8px;
    font-size: 0.82rem;
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

@media (min-width: 640px) {
    .tab {
        padding: 10px 14px;
        font-size: 0.86rem;
    }
}

/* -------- News scroll area -------- */
.news-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}
.news-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 6px;
    max-height: 420px;
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

@media (min-width: 640px) {
    .news-scroll {
        max-height: 380px;
        padding: 8px;
    }
}
@media (min-width: 1024px) {
    .news-scroll {
        max-height: 430px;
    }
}
@media (min-width: 1280px) {
    .news-scroll {
        max-height: 470px;
    }
}

/* -------- News row -------- */
.news-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 8px;
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

/* Rank */
.news-rank {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.72rem;
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
    width: 66px;
    height: 48px;
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
    font-size: 0.84rem;
    font-weight: 500;
    line-height: 1.45;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

/* Responsive thumb/title scaling */
@media (min-width: 640px) {
    .news-row {
        padding: 10px;
        gap: 11px;
    }
    .news-thumb-wrap {
        width: 78px;
        height: 56px;
    }
    .news-title {
        font-size: 0.88rem;
    }
    .news-rank {
        width: 26px;
        height: 26px;
        font-size: 0.76rem;
    }
}
@media (min-width: 1024px) {
    .news-thumb-wrap {
        width: 84px;
        height: 60px;
    }
    .news-title {
        font-size: 0.88rem;
    }
}
@media (min-width: 1280px) {
    .news-thumb-wrap {
        width: 92px;
        height: 66px;
    }
    .news-title {
        font-size: 0.92rem;
    }
}

/* -------- Empty state -------- */
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

/* ============================================================
   TRANSITIONS
   ============================================================ */
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

/* ============================================================
   TINY PHONES (≤ 380px)
   ============================================================ */
@media (max-width: 380px) {
    .tab {
        font-size: 0.76rem;
        padding: 7px 6px;
        gap: 4px;
    }
    .news-thumb-wrap {
        width: 56px;
        height: 42px;
    }
    .news-title {
        font-size: 0.78rem;
    }
    .news-rank {
        width: 20px;
        height: 20px;
        font-size: 0.66rem;
    }
    .news-row {
        padding: 7px 6px;
        gap: 8px;
    }
    .video-badge {
        font-size: 0.66rem;
        padding: 4px 9px;
    }
    .video-play-button {
        width: 52px;
        height: 52px;
        font-size: 20px;
    }
    .video-caption {
        font-size: 0.82rem;
        padding: 10px 12px;
    }
}

/* ============================================================
   REDUCED MOTION
   ============================================================ */
@media (prefers-reduced-motion: reduce) {
    .live-dot {
        animation: none;
    }
    .news-thumb,
    .news-row,
    .video-poster img,
    .video-play-button {
        transition: none;
    }
    .fade-list-enter-active,
    .fade-list-leave-active {
        transition: opacity 0.15s;
    }
}
</style>
