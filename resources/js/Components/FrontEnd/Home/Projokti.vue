<script setup>
import { Swiper, SwiperSlide } from "swiper/vue";
import { Autoplay, Navigation, Pagination } from "swiper/modules";
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

import "swiper/css";
import "swiper/css/navigation";
import "swiper/css/pagination";

const props = defineProps({
    techCategories: {
        type: Array,
        default: () => [],
    },
});

// Safe category finder — handles missing array
const technology = computed(() =>
    (props.techCategories ?? []).find((c) => c.slug === "przukti"),
);

const entertainment = computed(() =>
    (props.techCategories ?? []).find((c) => c.slug === "binodn"),
);

// Swiper refs for navigation
const prevEl = ref(null);
const nextEl = ref(null);

const swiperModules = [Autoplay, Navigation, Pagination];

// ---------- Safe helpers ----------
function imgUrl(path) {
    if (!path || !String(path).trim()) return "/images/default.jpg";
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}

function stripHtml(html) {
    if (!html) return "";
    return String(html).replace(/<[^>]*>/g, "");
}

function descCut(html, len = 150) {
    const text = stripHtml(html);
    return text.length > len ? text.slice(0, len) + " ..." : text;
}

function truncate(text, words = 10) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}
</script>

<template>
    <section class="tech-section py-6 sm:py-8">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div
                class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6 items-stretch"
            >
                <!-- =============================================
                     TECHNOLOGY SECTION (2/3 on desktop)
                ============================================== -->
                <div
                    v-if="technology"
                    class="lg:col-span-2 flex flex-col min-w-0"
                >
                    <!-- Section header -->
                    <header class="section-header">
                        <div class="header-icon">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <h2 class="header-title">{{ technology.name }}</h2>
                        <Link
                            :href="route('all.news', technology.slug)"
                            class="header-more"
                            aria-label="View all"
                        >
                            সব দেখুন
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <!-- Featured -->
                    <article
                        v-if="technology.news?.length"
                        class="featured-block"
                    >
                        <div class="featured-img-col">
                            <Link
                                :href="
                                    route(
                                        'news.details',
                                        technology.news[0].slug,
                                    )
                                "
                                class="featured-img-wrap"
                            >
                                <img
                                    class="featured-img"
                                    :src="imgUrl(technology.news[0].image)"
                                    :alt="technology.news[0].title"
                                    loading="lazy"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                                <span class="featured-overlay"></span>
                            </Link>
                        </div>

                        <div class="featured-content-col">
                            <span class="featured-badge">
                                <i class="fa-solid fa-bolt"></i>
                                ফিচার্ড
                            </span>

                            <Link
                                :href="
                                    route(
                                        'news.details',
                                        technology.news[0].slug,
                                    )
                                "
                                class="featured-title"
                            >
                                {{ truncate(technology.news[0].title, 14) }}
                            </Link>

                            <p class="featured-desc">
                                {{
                                    descCut(technology.news[0].description, 180)
                                }}
                            </p>

                            <Link
                                :href="
                                    route(
                                        'news.details',
                                        technology.news[0].slug,
                                    )
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

                    <!-- Carousel -->
                    <div class="carousel-wrap">
                        <div class="carousel-header">
                            <h3 class="carousel-title">
                                <i class="fa-solid fa-layer-group"></i>
                                আরও টেক নিউজ
                            </h3>

                            <div class="carousel-nav">
                                <button
                                    ref="prevEl"
                                    class="nav-btn"
                                    aria-label="Previous"
                                >
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <button
                                    ref="nextEl"
                                    class="nav-btn"
                                    aria-label="Next"
                                >
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <Swiper
                            v-if="(technology.news?.length ?? 0) > 1"
                            :modules="swiperModules"
                            :slides-per-view="1"
                            :space-between="12"
                            :breakpoints="{
                                480: { slidesPerView: 2, spaceBetween: 12 },
                                768: { slidesPerView: 3, spaceBetween: 12 },
                                1024: { slidesPerView: 4, spaceBetween: 12 },
                            }"
                            :autoplay="{
                                delay: 4000,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            }"
                            :navigation="{
                                prevEl: prevEl,
                                nextEl: nextEl,
                            }"
                            :pagination="{
                                clickable: true,
                                dynamicBullets: true,
                            }"
                            :loop="(technology.news?.length ?? 0) > 4"
                            :grab-cursor="true"
                            class="tech-swiper"
                        >
                            <SwiperSlide
                                v-for="item in technology.news?.slice(1, 9) ??
                                []"
                                :key="item.id"
                            >
                                <Link
                                    :href="route('news.details', item.slug)"
                                    class="slide-card"
                                >
                                    <div class="slide-img-wrap">
                                        <img
                                            class="slide-img"
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
                                    <p class="slide-title">
                                        {{ truncate(item.title, 9) }}
                                    </p>
                                </Link>
                            </SwiperSlide>
                        </Swiper>

                        <div v-else class="carousel-empty">
                            <i class="fa-regular fa-newspaper"></i>
                            <p>আরও সংবাদ নেই</p>
                        </div>
                    </div>
                </div>

                <!-- =============================================
                     ENTERTAINMENT SECTION (1/3 on desktop)
                ============================================== -->
                <aside
                    v-if="entertainment"
                    class="lg:col-span-1 flex flex-col min-w-0"
                >
                    <header class="section-header entertainment">
                        <div class="header-icon">
                            <i class="fa-solid fa-clapperboard"></i>
                        </div>
                        <h2 class="header-title">{{ entertainment.name }}</h2>
                        <Link
                            :href="route('all.news', entertainment.slug)"
                            class="header-more"
                            aria-label="View all"
                        >
                            সব দেখুন
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <div class="ent-card">
                        <!-- Featured -->
                        <article
                            v-if="entertainment.news?.length"
                            class="ent-featured"
                        >
                            <Link
                                :href="
                                    route(
                                        'news.details',
                                        entertainment.news[0].slug,
                                    )
                                "
                                class="ent-img-wrap"
                            >
                                <img
                                    class="ent-img"
                                    :src="imgUrl(entertainment.news[0].image)"
                                    :alt="entertainment.news[0].title"
                                    loading="lazy"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                                <span class="ent-overlay"></span>
                                <span class="ent-play">
                                    <i class="fa-solid fa-play"></i>
                                </span>
                            </Link>

                            <Link
                                :href="
                                    route(
                                        'news.details',
                                        entertainment.news[0].slug,
                                    )
                                "
                                class="ent-title"
                            >
                                {{ truncate(entertainment.news[0].title, 12) }}
                            </Link>
                        </article>

                        <!-- List -->
                        <div class="ent-list">
                            <Link
                                v-for="item in entertainment.news?.slice(
                                    1,
                                    4,
                                ) ?? []"
                                :key="item.id"
                                :href="route('news.details', item.slug)"
                                class="ent-item"
                            >
                                <div class="ent-thumb-wrap">
                                    <img
                                        class="ent-thumb"
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
                                <p class="ent-item-title">
                                    {{ truncate(item.title, 9) }}
                                </p>
                            </Link>

                            <!-- Empty -->
                            <div
                                v-if="(entertainment.news?.length ?? 0) <= 1"
                                class="ent-empty"
                            >
                                <i class="fa-regular fa-newspaper"></i>
                                <p>আরও সংবাদ নেই</p>
                            </div>
                        </div>

                        <!-- Footer CTA -->
                        <Link
                            :href="route('all.news', entertainment.slug)"
                            class="ent-cta"
                        >
                            সব বিনোদন সংবাদ
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>

<style scoped>
aside {
    background-color: transparent;
}
/* ==================== SECTION ==================== */
.tech-section {
    background: #f8fafc;
}
h2 {
    margin-bottom: 0px;
}

/* ==================== SECTION HEADER ==================== */
.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: linear-gradient(90deg, #7c3aed 0%, #a855f7 100%);
    border-radius: 10px 10px 0 0;
    color: #fff;
    margin-bottom: 12px;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

.section-header.entertainment {
    background: linear-gradient(90deg, #db2777 0%, #ec4899 100%);
    box-shadow: 0 4px 12px rgba(219, 39, 119, 0.2);
}

.header-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.22);
    font-size: 14px;
    flex-shrink: 0;
}

.header-title {
    flex: 1;
    min-width: 0;
    font-weight: 700;
    font-size: 1rem;
    line-height: 1.2;
    letter-spacing: 0.2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (min-width: 640px) {
    .section-header {
        padding: 12px 18px;
    }
    .header-title {
        font-size: 1.1rem;
    }
    .header-icon {
        width: 34px;
        height: 34px;
        font-size: 15px;
    }
}

.header-more {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    transition:
        background 0.2s,
        gap 0.2s;
}

.header-more:hover {
    background: rgba(255, 255, 255, 0.35);
    gap: 7px;
}

/* ==================== FEATURED BLOCK ==================== */
.featured-block {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    display: grid;
    grid-template-columns: 1fr;
    margin-bottom: 16px;
    transition: box-shadow 0.3s ease;
}

.featured-block:hover {
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 16px 36px rgba(124, 58, 237, 0.1);
}

@media (min-width: 768px) {
    .featured-block {
        grid-template-columns: 1.2fr 1fr;
    }
}

.featured-img-col {
    min-width: 0;
}

.featured-img-wrap {
    display: block;
    width: 100%;
    height: 100%;
    min-height: 220px;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
    position: relative;
    isolation: isolate;
}

@media (min-width: 768px) {
    .featured-img-wrap {
        aspect-ratio: auto;
        height: 100%;
    }
}

.featured-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.featured-img-wrap:hover .featured-img {
    transform: scale(1.05);
}

.featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 60%,
        rgba(0, 0, 0, 0.3) 100%
    );
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.featured-img-wrap:hover .featured-overlay {
    opacity: 1;
}

.featured-content-col {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    justify-content: center;
    min-width: 0;
}

@media (min-width: 768px) {
    .featured-content-col {
        padding: 20px;
    }
}

.featured-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    align-self: flex-start;
    padding: 4px 10px;
    border-radius: 999px;
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}

.featured-badge i {
    font-size: 0.68rem;
}

.featured-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 1.05rem;
    line-height: 1.35;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.featured-title:hover {
    color: #7c3aed;
}

@media (min-width: 768px) {
    .featured-title {
        font-size: 1.25rem;
    }
}

@media (min-width: 1024px) {
    .featured-title {
        font-size: 1.35rem;
    }
}

.featured-desc {
    color: #475569;
    font-size: 0.85rem;
    line-height: 1.6;
    text-align: justify;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

@media (min-width: 768px) {
    .featured-desc {
        font-size: 0.9rem;
    }
}

/* ==================== READ MORE LINK ==================== */
.read-more {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    align-self: flex-start;
    color: #7c3aed;
    font-size: 0.85rem;
    font-weight: 600;
    transition:
        gap 0.2s,
        color 0.2s;
    margin-top: 4px;
}

.read-more:hover {
    color: #5b21b6;
    gap: 8px;
}

/* ==================== CAROUSEL ==================== */
.carousel-wrap {
    background: #ffffff;
    border-radius: 12px;
    padding: 14px;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
}

.carousel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    gap: 10px;
}

.carousel-title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.carousel-title i {
    color: #7c3aed;
    font-size: 0.85rem;
}

.carousel-nav {
    display: flex;
    gap: 6px;
}

.nav-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s,
        border-color 0.2s;
    font-size: 12px;
}

.nav-btn:hover {
    background: #7c3aed;
    color: #fff;
    border-color: #7c3aed;
}

/* Swiper container */
.tech-swiper {
    padding-bottom: 30px !important;
}

/* Slide card */
.slide-card {
    display: block;
    text-decoration: none;
    background: #f8fafc;
    border-radius: 10px;
    overflow: hidden;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
    border: 1px solid #e2e8f0;
    height: 100%;
}

.slide-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.15);
}

.slide-img-wrap {
    width: 100%;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
}

.slide-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.slide-card:hover .slide-img {
    transform: scale(1.08);
}

.slide-title {
    padding: 10px;
    margin: 0;
    color: #1e293b;
    font-size: 0.82rem;
    line-height: 1.6;
    font-weight: 500;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s;
}

.slide-card:hover .slide-title {
    color: #7c3aed;
}

/* Empty carousel */
.carousel-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
    color: #94a3b8;
    border: 1px dashed #e2e8f0;
    border-radius: 10px;
    gap: 8px;
}

.carousel-empty i {
    font-size: 26px;
    opacity: 0.6;
}

.carousel-empty p {
    font-size: 0.82rem;
    margin: 0;
}

/* Swiper navigation styling — override default */
.tech-swiper :deep(.swiper-button-prev),
.tech-swiper :deep(.swiper-button-next) {
    display: none;
}

.tech-swiper :deep(.swiper-pagination) {
    bottom: 0 !important;
}

.tech-swiper :deep(.swiper-pagination-bullet) {
    background: #cbd5e1;
    opacity: 0.6;
    transition:
        background 0.2s,
        opacity 0.2s;
}

.tech-swiper :deep(.swiper-pagination-bullet-active) {
    background: #7c3aed;
    opacity: 1;
    width: 20px;
    border-radius: 4px;
}

/* ==================== ENTERTAINMENT CARD ==================== */
.ent-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
}

.ent-featured {
    padding: 12px 12px 0;
}

.ent-img-wrap {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    border-radius: 10px;
    overflow: hidden;
    background: #e2e8f0;
    position: relative;
    isolation: isolate;
}

.ent-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.ent-img-wrap:hover .ent-img {
    transform: scale(1.06);
}

.ent-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 50%,
        rgba(0, 0, 0, 0.35) 100%
    );
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.ent-img-wrap:hover .ent-overlay {
    opacity: 1;
}

.ent-play {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.85);
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: rgba(236, 72, 153, 0.92);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    opacity: 0;
    transition:
        transform 0.25s,
        opacity 0.25s;
    pointer-events: none;
}

.ent-img-wrap:hover .ent-play {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
}

.ent-title {
    display: block;
    padding: 10px 0 4px;
    color: #0f172a;
    font-size: 0.95rem;
    font-weight: 700;
    line-height: 1.7;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ent-title:hover {
    color: #db2777;
}

@media (min-width: 640px) {
    .ent-title {
        font-size: 1rem;
    }
}

/* List */
.ent-list {
    padding: 0 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
}

.ent-item {
    display: flex;
    gap: 10px;
    padding: 8px 4px;
    align-items: flex-start;
    text-decoration: none;
    border-bottom: 1px dashed #e2e8f0;
    border-radius: 4px;
    transition: background 0.15s;
}

.ent-item:hover {
    background: #fdf2f8;
}

.ent-item:hover .ent-item-title {
    color: #db2777;
}

.ent-item:last-of-type {
    border-bottom: none;
}

.ent-thumb-wrap {
    flex-shrink: 0;
    width: 74px;
    height: 54px;
    border-radius: 6px;
    overflow: hidden;
    background: #e2e8f0;
}

@media (min-width: 640px) {
    .ent-thumb-wrap {
        width: 82px;
        height: 60px;
    }
}

.ent-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.ent-item:hover .ent-thumb {
    transform: scale(1.08);
}

.ent-item-title {
    flex: 1;
    color: #1e293b;
    font-size: 0.82rem;
    line-height: 1.6;
    font-weight: 500;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    padding-top: 2px;
}

@media (min-width: 640px) {
    .ent-item-title {
        font-size: 0.86rem;
    }
}

/* Empty */
.ent-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 18px 8px;
    color: #94a3b8;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    margin: 6px 0;
}

.ent-empty i {
    font-size: 22px;
    opacity: 0.6;
}

.ent-empty p {
    font-size: 0.78rem;
    margin: 0;
}

/* CTA */
.ent-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin: 10px 12px 12px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #db2777, #ec4899);
    color: #fff;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 8px;
    text-decoration: none;
    transition:
        transform 0.2s,
        box-shadow 0.2s,
        gap 0.2s;
    box-shadow: 0 4px 12px rgba(219, 39, 119, 0.25);
}

.ent-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(219, 39, 119, 0.35);
    gap: 9px;
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 360px) {
    .section-header {
        padding: 9px 10px;
    }
    .header-title {
        font-size: 0.92rem;
    }
    .header-more {
        font-size: 0.72rem;
        padding: 3px 8px;
    }
    .header-more span {
        display: none;
    }
    .featured-title {
        font-size: 0.95rem;
    }
    .slide-title {
        font-size: 0.78rem;
    }
    .ent-thumb-wrap {
        width: 64px;
        height: 48px;
    }
    .ent-item-title {
        font-size: 0.78rem;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .featured-content-col {
        padding: 24px;
    }
    .featured-title {
        font-size: 1.45rem;
    }
    .featured-desc {
        font-size: 0.95rem;
    }
    .ent-thumb-wrap {
        width: 90px;
        height: 66px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .featured-img,
    .slide-img,
    .slide-card,
    .ent-img,
    .ent-thumb,
    .ent-cta,
    .read-more,
    .header-more,
    .nav-btn {
        transition: none !important;
    }
    .slide-card:hover {
        transform: none;
    }
    .featured-img-wrap:hover .featured-img,
    .slide-card:hover .slide-img,
    .ent-img-wrap:hover .ent-img,
    .ent-item:hover .ent-thumb {
        transform: none;
    }
}
/* Equal-height technology and entertainment sections */
@media (min-width: 1024px) {
    .tech-section .container > .grid {
        align-items: stretch;
    }

    .tech-section .grid > div.lg\:col-span-2,
    .tech-section .grid > aside.lg\:col-span-1 {
        min-width: 0;
        height: 100%;
    }

    /* Left section */
    .tech-section .grid > div.lg\:col-span-2 {
        display: flex;
        flex-direction: column;
    }

    .tech-section .featured-block {
        flex-shrink: 0;
    }

    .tech-section .carousel-wrap {
        flex: 1;
        min-height: 0;
    }

    /* Right section */
    .tech-section .grid > aside.lg\:col-span-1 {
        display: flex;
        flex-direction: column;
    }

    .tech-section .ent-card {
        flex: 1;
        min-height: 0;
    }

    .tech-section .ent-list {
        flex: 1;
    }
}
</style>
