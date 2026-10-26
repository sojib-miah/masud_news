<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    rajcategories: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

// Prefer prop if passed; otherwise use page props
const categories = computed(() =>
    props.rajcategories?.length
        ? props.rajcategories
        : (page.props.rajcategories ?? []),
);

// ---------- Text helpers ----------
function stripHtml(html) {
    if (!html) return "";
    return String(html).replace(/<[^>]*>/g, "");
}

function truncateText(html, wordLimit = 50) {
    const text = stripHtml(html);
    const words = text.trim().split(/\s+/);
    return words.length > wordLimit
        ? words.slice(0, wordLimit).join(" ") + " ..."
        : text;
}

function truncateTitle(text, words = 10) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}

// ---------- Safe image resolver ----------
function imgUrl(path) {
    if (!path || !String(path).trim()) return "/images/default.jpg";
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}
</script>

<template>
    <section class="politic-section py-6 sm:py-8">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6"
            >
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
                            class="cat-more"
                            aria-label="View all"
                        >
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <!-- ==================== CARD BODY ==================== -->
                    <div class="cat-body">
                        <!-- FEATURED -->
                        <div
                            v-if="category.news?.length"
                            class="featured-block"
                        >
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
                                <span class="featured-cta">
                                    <i class="fa-solid fa-play"></i>
                                </span>
                            </Link>

                            <div class="featured-body">
                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
                                        )
                                    "
                                    class="featured-title"
                                >
                                    {{
                                        truncateTitle(
                                            category.news[0].title,
                                            12,
                                        )
                                    }}
                                </Link>

                                <p class="featured-desc">
                                    {{
                                        truncateText(
                                            category.news[0]?.description,
                                            28,
                                        )
                                    }}
                                </p>

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

                        <!-- LIST -->
                        <div class="list-block">
                            <Link
                                v-for="news in category.news?.slice(1, 6) ?? []"
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
                                    {{ truncateTitle(news.title, 9) }}
                                </p>
                            </Link>

                            <!-- Empty state -->
                            <div
                                v-if="(category.news?.length ?? 0) <= 1"
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
.politic-section {
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
    display: flex;
    flex-direction: column;
    transition:
        box-shadow 0.3s ease,
        transform 0.3s ease;
    height: 100%;
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
    background: linear-gradient(90deg, #01284f 0%, #024a8b 100%);
    position: relative;
}

.cat-header::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
    background: #4fa8ff;
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
        font-size: 1.08rem;
    }
}

@media (min-width: 1024px) {
    .cat-title {
        font-size: 1.12rem;
    }
}

.cat-more {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    transition:
        background 0.2s,
        transform 0.2s;
    flex-shrink: 0;
}

.cat-more:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateX(2px);
}

/* ==================== BODY ==================== */
.cat-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1;
}

/* ==================== FEATURED ==================== */
.featured-block {
    display: flex;
    flex-direction: column;
    gap: 10px;
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

.featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 40%,
        rgba(0, 0, 0, 0.35) 100%
    );
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.featured-img-wrap:hover .featured-overlay {
    opacity: 1;
}

.featured-cta {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(0, 147, 12, 0.9);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    transform: scale(0.9);
    opacity: 0;
    transition:
        transform 0.25s,
        opacity 0.25s;
}

.featured-img-wrap:hover .featured-cta {
    transform: scale(1);
    opacity: 1;
}

.featured-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.featured-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1.7;
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
        font-size: 1rem;
    }
}

.featured-desc {
    color: #475569;
    font-size: 0.83rem;
    line-height: 1.7;
    text-align: justify;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 0;
}

@media (min-width: 640px) {
    .featured-desc {
        font-size: 0.85rem;
    }
}

/* ==================== LIST ==================== */
.list-block {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 4px;
}

.list-item {
    display: flex;
    gap: 10px;
    padding: 8px 0;
    align-items: flex-start;
    text-decoration: none;
    border-bottom: 1px dashed #e2e8f0;
    border-radius: 4px;
    padding-left: 4px;
    padding-right: 4px;
    transition: background 0.15s;
}

.list-item:hover {
    background: #f0fdf4;
}

.list-item:hover .list-title {
    color: #00930c;
}

.list-item:last-of-type {
    border-bottom: none;
}

.list-thumb-wrap {
    flex-shrink: 0;
    width: 78px;
    height: 56px;
    border-radius: 6px;
    overflow: hidden;
    background: #e2e8f0;
}

@media (min-width: 640px) {
    .list-thumb-wrap {
        width: 84px;
        height: 60px;
    }
}

@media (min-width: 1024px) {
    .list-thumb-wrap {
        width: 88px;
        height: 62px;
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
    font-size: 0.83rem;
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
    .list-title {
        font-size: 0.86rem;
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
    padding-top: 8px;
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
@media (max-width: 360px) {
    .cat-header {
        padding: 10px 12px;
    }
    .cat-title {
        font-size: 0.93rem;
    }
    .featured-title {
        font-size: 0.88rem;
    }
    .featured-desc {
        font-size: 0.78rem;
    }
    .list-thumb-wrap {
        width: 64px;
        height: 48px;
    }
    .list-title {
        font-size: 0.78rem;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .cat-body {
        padding: 16px;
        gap: 14px;
    }
    .list-thumb-wrap {
        width: 94px;
        height: 68px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .cat-card,
    .featured-img,
    .list-thumb,
    .read-more,
    .cat-more,
    .featured-cta {
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
