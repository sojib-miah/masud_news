<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    homeCategories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

// Prefer prop if passed, otherwise fall back to page props
const categories = computed(() =>
    props.homeCategories?.length
        ? props.homeCategories
        : (page.props.homeCategories ?? []),
);

// Safe image URL resolver
function imgUrl(path) {
    if (!path || !String(path).trim()) return "/images/default.jpg";
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}

// Title truncation
function truncate(text, words = 10) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}

// Safe news slice
function listOf(cat, from = 1, to = 5) {
    return cat?.news?.slice(from, to) ?? [];
}
</script>

<template>
    <section class="home-cats py-6 sm:py-8">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
                <article
                    v-for="category in categories"
                    :key="category.id"
                    class="cat-card"
                >
                    <!-- ==================== HEADER ==================== -->
                    <header class="cat-header">
                        <Link
                            :href="route('all.news', category.slug)"
                            class="cat-title"
                        >
                            {{ category.name }}
                        </Link>
                        <Link
                            :href="route('all.news', category.slug)"
                            class="cat-see-all"
                            aria-label="View all"
                        >
                            সব দেখুন
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <!-- ==================== BODY ==================== -->
                    <div class="cat-body">
                        <!-- FEATURED (first news) -->
                        <div v-if="category.news?.length" class="featured-col">
                            <Link
                                :href="
                                    route('news.details', category.news[0].slug)
                                "
                                class="featured-img-wrap"
                            >
                                <img
                                    class="featured-img"
                                    :src="imgUrl(category.news[0].image)"
                                    :alt="category.news[0].title"
                                    loading="lazy"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                                <span class="featured-overlay"></span>
                            </Link>

                            <div class="featured-body">
                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
                                        )
                                    "
                                    class="featured-title leading-7"
                                >
                                    {{ truncate(category.news[0].title, 12) }}
                                </Link>

                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
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
                        </div>

                        <!-- LIST (remaining news) -->
                        <div class="list-col">
                            <Link
                                v-for="news in listOf(category, 1, 5)"
                                :key="news.id"
                                :href="route('news.details', news.slug)"
                                class="list-item"
                            >
                                <div class="list-thumb-wrap">
                                    <img
                                        class="list-thumb"
                                        :src="imgUrl(news.image)"
                                        :alt="news.title"
                                        loading="lazy"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                    />
                                </div>

                                <p class="list-title">
                                    {{ truncate(news.title, 9) }}
                                </p>
                            </Link>

                            <!-- Empty list state -->
                            <div
                                v-if="listOf(category, 1, 5).length === 0"
                                class="list-empty"
                            >
                                <i class="fa-regular fa-newspaper"></i>
                                <p>আরও সংবাদ নেই</p>
                            </div>

                            <!-- Footer link -->
                            <div class="list-footer">
                                <Link
                                    :href="route('all.news', category.slug)"
                                    class="read-more"
                                >
                                    আরো সংবাদ
                                    <i
                                        class="fa-solid fa-angles-right text-[0.65rem]"
                                    ></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* ==================== SECTION ==================== */
.home-cats {
    background: #f1f5f9;
}

/* ==================== CATEGORY CARD ==================== */
.cat-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    transition:
        box-shadow 0.3s ease,
        transform 0.3s ease;
    display: flex;
    flex-direction: column;
}

.cat-card:hover {
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 16px 36px rgba(0, 147, 12, 0.1);
    transform: translateY(-2px);
}

/* ==================== HEADER ==================== */
.cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(90deg, #00930c 0%, #00b515 100%);
    position: relative;
}

.cat-header::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
    background: #8aff90;
}

.cat-title {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    line-height: 1.2;
    letter-spacing: 0.2px;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: opacity 0.2s;
}

.cat-title:hover {
    opacity: 0.85;
}

@media (min-width: 640px) {
    .cat-title {
        font-size: 1.1rem;
    }
}

@media (min-width: 1024px) {
    .cat-title {
        font-size: 1.15rem;
    }
}

.cat-see-all {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: rgba(255, 255, 255, 0.92);
    font-size: 0.78rem;
    font-weight: 600;
    white-space: nowrap;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.15);
    transition:
        background 0.2s,
        color 0.2s,
        gap 0.2s;
    flex-shrink: 0;
}

.cat-see-all:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #fff;
    gap: 7px;
}

/* ==================== BODY (grid) ==================== */
.cat-body {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px;
}

@media (min-width: 480px) {
    .cat-body {
        grid-template-columns: 1fr 1fr;
    }
}

/* ==================== FEATURED COLUMN ==================== */
.featured-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.featured-img-wrap {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    border-radius: 8px;
    overflow: hidden;
    background: #e2e8f0;
    position: relative;
    isolation: isolate;
}

.featured-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.featured-img-wrap:hover .featured-img {
    transform: scale(1.06);
}

/* subtle vignette on hover */
.featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 50%,
        rgba(0, 0, 0, 0.15) 100%
    );
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.featured-img-wrap:hover .featured-overlay {
    opacity: 1;
}

.featured-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding-top: 10px;
}

.featured-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 0.92rem;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.featured-title:hover {
    color: #00930c;
}

@media (min-width: 640px) {
    .featured-title {
        font-size: 0.98rem;
    }
}

@media (min-width: 1024px) {
    .featured-title {
        font-size: 1rem;
    }
}

/* ==================== LIST COLUMN ==================== */
.list-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.list-item {
    display: flex;
    gap: 10px;
    padding: 6px 0;
    align-items: flex-start;
    text-decoration: none;
    border-bottom: 1px dashed #e2e8f0;
    transition: background 0.15s;
    border-radius: 4px;
    padding-left: 4px;
    padding-right: 4px;
}

.list-item:last-child {
    border-bottom: none;
}

.list-item:hover {
    background: #f0fdf4;
}

.list-item:hover .list-title {
    color: #00930c;
}

.list-thumb-wrap {
    flex-shrink: 0;
    width: 74px;
    height: 56px;
    border-radius: 6px;
    overflow: hidden;
    background: #e2e8f0;
}

@media (min-width: 640px) {
    .list-thumb-wrap {
        width: 82px;
        height: 60px;
    }
}

@media (min-width: 1024px) {
    .list-thumb-wrap {
        width: 90px;
        height: 66px;
    }
}

.list-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.list-item:hover .list-thumb {
    transform: scale(1.08);
}

.list-title {
    flex: 1;
    color: #1e293b;
    font-size: 0.82rem;
    line-height: 1.5;
    font-weight: 500;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    padding-top: 2px;
}

@media (min-width: 640px) {
    .list-title {
        font-size: 0.85rem;
    }
}

@media (min-width: 1024px) {
    .list-title {
        font-size: 0.87rem;
    }
}

/* ==================== EMPTY STATE ==================== */
.list-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 18px 8px;
    color: #94a3b8;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
}

.list-empty i {
    font-size: 22px;
    opacity: 0.6;
}

.list-empty p {
    font-size: 0.78rem;
    margin: 0;
}

/* ==================== FOOTER LINK ==================== */
.list-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: auto;
    padding-top: 6px;
}

.read-more {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #00930c;
    font-size: 0.8rem;
    font-weight: 600;
    transition:
        gap 0.2s,
        color 0.2s;
    white-space: nowrap;
}

.read-more:hover {
    color: #006b09;
    gap: 7px;
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 359px) {
    .cat-header {
        padding: 10px 12px;
    }
    .cat-title {
        font-size: 0.95rem;
    }
    .cat-see-all {
        font-size: 0.72rem;
        padding: 3px 8px;
    }
    .featured-title {
        font-size: 0.88rem;
    }
    .list-thumb-wrap {
        width: 62px;
        height: 48px;
    }
    .list-title {
        font-size: 0.76rem;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .cat-body {
        gap: 14px;
        padding: 14px;
    }
    .featured-img-wrap {
        aspect-ratio: 16 / 9;
    }
    .list-thumb-wrap {
        width: 96px;
        height: 70px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .cat-card,
    .featured-img,
    .list-thumb,
    .read-more,
    .cat-see-all {
        transition: none;
    }
    .cat-card:hover {
        transform: none;
    }
    .featured-img-wrap:hover .featured-img,
    .list-item:hover .list-thumb {
        transform: none;
    }
}
</style>
