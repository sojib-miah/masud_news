<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    subCategories: {
        type: Array,
        default: () => [],
    },
});

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

// Build sub-category URL safely
function subLink(category) {
    if (!category?.category?.slug || !category?.slug) return "#";
    return route("all.news", {
        category: category.category.slug,
        subcategory: category.slug,
    });
}
</script>

<template>
    <section class="subcat-section py-6 sm:py-8">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">
                <article
                    v-for="category in subCategories"
                    :key="category.id"
                    class="subcat-card"
                >
                    <!-- ==================== HEADER ==================== -->
                    <header class="subcat-header">
                        <Link :href="subLink(category)" class="subcat-title">
                            {{ category.name }}
                        </Link>
                        <Link
                            :href="subLink(category)"
                            class="subcat-more"
                            aria-label="View all"
                        >
                            সব দেখুন
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <!-- ==================== BODY ==================== -->
                    <div class="subcat-body">
                        <!-- FEATURED (first news) -->
                        <div
                            v-if="category.news && category.news.length > 0"
                            class="featured-col"
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
                                    {{ truncate(category.news[0].title, 10) }}
                                </Link>

                                <p class="featured-desc">
                                    {{
                                        descCut(
                                            category.news[0].description,
                                            130,
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

                        <!-- Empty featured state -->
                        <div v-else class="featured-empty">
                            <i class="fa-regular fa-newspaper"></i>
                            <p>কোনো সংবাদ নেই</p>
                        </div>

                        <!-- LIST (remaining news) -->
                        <div class="list-col">
                            <Link
                                v-for="post in category.news?.slice(1, 5) ?? []"
                                :key="post.id"
                                :href="route('news.details', post.slug)"
                                class="list-item"
                            >
                                <div class="list-thumb-wrap">
                                    <img
                                        class="list-thumb"
                                        :src="imgUrl(post.image)"
                                        :alt="post.title"
                                        loading="lazy"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                    />
                                </div>

                                <p class="list-title">
                                    {{ truncate(post.title, 9) }}
                                </p>
                            </Link>

                            <!-- Empty list state -->
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
                                    :href="subLink(category)"
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
.subcat-section {
    background: #f1f5f9;
}

/* ==================== CARD ==================== */
.subcat-card {
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

.subcat-card:hover {
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 16px 36px rgba(0, 147, 12, 0.1);
    transform: translateY(-2px);
}

/* ==================== HEADER ==================== */
.subcat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(90deg, #00930c 0%, #00b515 100%);
    position: relative;
}

.subcat-header::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
    background: #8aff90;
}

.subcat-title {
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

.subcat-title:hover {
    opacity: 0.85;
}

@media (min-width: 640px) {
    .subcat-title {
        font-size: 1.08rem;
    }
}

@media (min-width: 1024px) {
    .subcat-title {
        font-size: 1.12rem;
    }
}

.subcat-more {
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

.subcat-more:hover {
    background: rgba(255, 255, 255, 0.3);
    color: #fff;
    gap: 7px;
}

/* ==================== BODY ==================== */
.subcat-body {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px;
    flex: 1;
}

@media (min-width: 480px) {
    .subcat-body {
        grid-template-columns: 1fr 1fr;
    }
}

/* ==================== FEATURED ==================== */
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

.featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 50%,
        rgba(0, 0, 0, 0.3) 100%
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
        font-size: 0.98rem;
    }
}

@media (min-width: 1024px) {
    .featured-title {
        font-size: 1rem;
    }
}

.featured-desc {
    color: #475569;
    font-size: 0.83rem;
    line-height: 1.6;
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

/* Featured empty */
.featured-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 30px 12px;
    color: #94a3b8;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    min-height: 180px;
}

.featured-empty i {
    font-size: 28px;
    opacity: 0.55;
}

.featured-empty p {
    font-size: 0.82rem;
    margin: 0;
}

/* ==================== LIST ==================== */
.list-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.list-item {
    display: flex;
    gap: 10px;
    padding: 8px 4px;
    align-items: flex-start;
    text-decoration: none;
    border-bottom: 1px dashed #e2e8f0;
    border-radius: 4px;
    transition: background 0.15s;
}

.list-item:first-child {
    padding-top: 0;
}

.list-item:last-of-type {
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
    font-size: 0.83rem;
    line-height: 1.6;
    font-weight: 500;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    padding-top: 2px;
    margin: 0;
}

@media (min-width: 640px) {
    .list-title {
        font-size: 0.86rem;
    }
}

/* ==================== EMPTY LIST ==================== */
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
    .subcat-header {
        padding: 10px 12px;
    }
    .subcat-title {
        font-size: 0.93rem;
    }
    .subcat-more {
        font-size: 0.72rem;
        padding: 3px 8px;
    }
    .subcat-more span {
        display: none;
    }
    .featured-title {
        font-size: 0.88rem;
    }
    .featured-desc {
        font-size: 0.78rem;
    }
    .list-thumb-wrap {
        width: 62px;
        height: 48px;
    }
    .list-title {
        font-size: 0.78rem;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .subcat-body {
        gap: 14px;
        padding: 14px;
    }
    .list-thumb-wrap {
        width: 96px;
        height: 70px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .subcat-card,
    .featured-img,
    .list-thumb,
    .read-more,
    .subcat-more {
        transition: none;
    }
    .subcat-card:hover {
        transform: none;
    }
    .featured-img-wrap:hover .featured-img,
    .list-item:hover .list-thumb {
        transform: none;
    }
}
</style>
