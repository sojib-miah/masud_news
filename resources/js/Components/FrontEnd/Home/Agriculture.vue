<script setup>
import { Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const props = defineProps({
    mainCategories: {
        type: Array,
        default: () => [],
    },
    subCategory: {
        type: Object,
        default: () => null,
    },
});

// Safe image resolver
// function imgUrl(path, fallback = "/images/default.jpg") {
//     return path ? `/storage/${path}` : fallback;
// }
function imgUrl(path) {
    if (!path || typeof path !== "string" || !path.trim()) {
        return "/images/default.jpg";
    }

    return `/storage/${path}`;
}

function handleImageError(event) {
    event.target.onerror = null;
    event.target.src = "/images/default.jpg";
}

// Safe title truncation
function truncate(text, words = 10) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}

// Description: strip tags for measuring length, then re-close open tags
function descCut(html, len = 150) {
    if (!html) return "";

    // If it already contains HTML, keep it but trim text portion
    const hasTags = /<[^>]+>/.test(html);

    if (!hasTags) {
        return html.length > len ? html.slice(0, len) + "..." : html;
    }

    // Strip tags only to measure visible length
    const plain = html.replace(/<[^>]+>/g, "");
    if (plain.length <= len) return html;

    // Rebuild truncated HTML by walking through original string
    let visible = 0;
    let out = "";
    let inTag = false;
    for (let i = 0; i < html.length && visible < len; i++) {
        const ch = html[i];
        if (ch === "<") inTag = true;
        if (ch === ">") {
            inTag = false;
            out += ch;
            continue;
        }
        out += ch;
        if (!inTag) visible++;
    }
    // Close any open tags that were cut
    const openTags = (out.match(/<([a-z][a-z0-9]*)\b[^>]*>/gi) || [])
        .map((t) => t.match(/<([a-z][a-z0-9]*)/i)[1])
        .filter(
            (t) =>
                !new RegExp(`</${t}>$`, "i").test(out) &&
                !["br", "img", "hr", "input"].includes(t.toLowerCase()),
        );
    openTags.reverse().forEach((t) => (out += `</${t}>`));
    return out + "...";
}

// Sub category link helper
function subLink(cat, sub) {
    return route("all.news", { category: cat.slug, subcategory: sub.slug });
}
</script>

<template>
    <section class="news-section py-6 sm:py-8">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-6">
                <!-- ==================== MAIN CATEGORIES ==================== -->
                <article
                    v-for="category in mainCategories"
                    :key="category.id"
                    class="cat-block"
                >
                    <!-- Header -->
                    <header class="cat-header">
                        <Link
                            :href="route('all.news', category.slug)"
                            class="cat-title"
                        >
                            {{ category.name }}
                        </Link>
                        <Link
                            :href="route('all.news', category.slug)"
                            class="cat-more-top"
                            aria-label="View all"
                        >
                            সব দেখুন
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <!-- Body: featured + list -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3">
                        <!-- Featured (first news) -->
                        <div
                            v-if="category.news && category.news.length > 0"
                            class="featured-card"
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
                                    alt="News Image"
                                    loading="lazy"
                                    @error="handleImageError"
                                />
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

                                <div
                                    class="featured-desc"
                                    v-html="
                                        descCut(
                                            category.news[0].description,
                                            130,
                                        )
                                    "
                                ></div>

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

                        <!-- List (remaining news) -->
                        <div class="list-card">
                            <div
                                v-for="post in category.news
                                    ? category.news.slice(1, 5)
                                    : []"
                                :key="post.id"
                                class="list-item"
                            >
                                <Link
                                    :href="route('news.details', post.slug)"
                                    class="list-thumb-wrap"
                                >
                                    <img
                                        class="list-thumb"
                                        :src="imgUrl(post.image)"
                                        alt="News Image"
                                        @error="handleImageError"
                                        loading="lazy"
                                    />
                                </Link>

                                <Link
                                    :href="route('news.details', post.slug)"
                                    class="list-title"
                                >
                                    {{ truncate(post.title, 9) }}
                                </Link>
                            </div>

                            <div class="list-footer">
                                <Link
                                    :href="route('all.news', category.slug)"
                                    class="read-more"
                                >
                                    আরো সংবাদ
                                    <i
                                        class="fa-solid fa-angle-right text-[0.7rem]"
                                    ></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- ==================== SUB CATEGORY (SAME STYLE) ==================== -->
                <article v-if="subCategory" class="cat-block">
                    <header class="cat-header">
                        <Link
                            :href="subLink(subCategory.category, subCategory)"
                            class="cat-title"
                        >
                            {{ subCategory.name }}
                        </Link>
                        <Link
                            :href="subLink(subCategory.category, subCategory)"
                            class="cat-more-top"
                            aria-label="View all"
                        >
                            সব দেখুন
                            <i
                                class="fa-solid fa-arrow-right text-[0.7rem]"
                            ></i>
                        </Link>
                    </header>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3">
                        <!-- Featured -->
                        <div
                            v-if="
                                subCategory.news && subCategory.news.length > 0
                            "
                            class="featured-card"
                        >
                            <Link
                                :href="
                                    route(
                                        'news.details',
                                        subCategory.news[0].slug,
                                    )
                                "
                                class="featured-img-wrap"
                            >
                                <img
                                    class="featured-img"
                                    :src="imgUrl(subCategory.news[0].image)"
                                    @error="handleImageError"
                                    alt="News Image"
                                    loading="lazy"
                                />
                            </Link>

                            <div class="featured-body">
                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            subCategory.news[0].slug,
                                        )
                                    "
                                    class="featured-title"
                                >
                                    {{
                                        truncate(subCategory.news[0].title, 10)
                                    }}
                                </Link>

                                <div
                                    class="featured-desc"
                                    v-html="
                                        descCut(
                                            subCategory.news[0].description,
                                            130,
                                        )
                                    "
                                ></div>

                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            subCategory.news[0].slug,
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

                        <!-- List -->
                        <div class="list-card">
                            <div
                                v-for="post in subCategory.news
                                    ? subCategory.news.slice(1, 5)
                                    : []"
                                :key="post.id"
                                class="list-item"
                            >
                                <Link
                                    :href="route('news.details', post.slug)"
                                    class="list-thumb-wrap"
                                >
                                    <img
                                        class="list-thumb"
                                        :src="imgUrl(post.image)"
                                        alt="News Image"
                                        loading="lazy"
                                        @error="handleImageError"
                                    />
                                </Link>

                                <Link
                                    :href="route('news.details', post.slug)"
                                    class="list-title"
                                >
                                    {{ truncate(post.title, 9) }}
                                </Link>
                            </div>

                            <div class="list-footer">
                                <Link
                                    :href="
                                        subLink(
                                            subCategory.category,
                                            subCategory,
                                        )
                                    "
                                    class="read-more"
                                >
                                    আরো সংবাদ
                                    <i
                                        class="fa-solid fa-angle-right text-[0.7rem]"
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
.news-section {
    background: #f1f5f9;
}

/* ==================== CATEGORY BLOCK ==================== */
.cat-block {
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 4px 16px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
}

.cat-block:hover {
    box-shadow:
        0 4px 10px rgba(0, 0, 0, 0.06),
        0 12px 28px rgba(0, 147, 12, 0.08);
}

/* ==================== HEADER ==================== */
.cat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(90deg, #00930c 0%, #00a815 100%);
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
    transition: opacity 0.2s;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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

.cat-title:hover {
    opacity: 0.85;
}

.cat-more-top {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.78rem;
    white-space: nowrap;
    padding: 4px 8px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.12);
    transition:
        background 0.2s,
        color 0.2s;
    flex-shrink: 0;
}

.cat-more-top:hover {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
}

/* ==================== FEATURED CARD ==================== */
.featured-card {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.featured-img-wrap {
    display: block;
    width: 100%;
    overflow: hidden;
    border-radius: 8px;
    background: #e5e7eb;
    aspect-ratio: 16 / 10;
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

.featured-body {
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.featured-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1.45;
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

/* Description — now renders HTML tags safely */
.featured-desc {
    color: #475569;
    font-size: 0.85rem;
    line-height: 1.6;
    text-align: justify;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Make sure injected HTML respects the container */
.featured-desc :deep(p) {
    margin: 0;
}

.featured-desc :deep(a) {
    color: #00930c;
    text-decoration: underline;
}

.featured-desc :deep(strong),
.featured-desc :deep(b) {
    font-weight: 600;
    color: #1e293b;
}

.featured-desc :deep(em),
.featured-desc :deep(i) {
    font-style: italic;
}

.featured-desc :deep(br) {
    display: none;
}

/* ==================== LIST CARD ==================== */
.list-card {
    background: #fff;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.list-item {
    display: flex;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px dashed #e2e8f0;
    align-items: flex-start;
}

.list-item:first-child {
    padding-top: 0;
}

.list-item:last-of-type {
    border-bottom: none;
}

.list-thumb-wrap {
    flex-shrink: 0;
    width: 80px;
    height: 64px;
    overflow: hidden;
    border-radius: 6px;
    background: #e5e7eb;
    display: block;
}

@media (min-width: 640px) {
    .list-thumb-wrap {
        width: 92px;
        height: 68px;
    }
}

.list-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.list-thumb-wrap:hover .list-thumb {
    transform: scale(1.08);
}

.list-title {
    color: #1e293b;
    font-size: 0.85rem;
    line-height: 1.45;
    font-weight: 500;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    padding-top: 2px;
}

.list-title:hover {
    color: #00930c;
}

@media (min-width: 640px) {
    .list-title {
        font-size: 0.88rem;
    }
}

/* ==================== FOOTER LINK ==================== */
.list-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: auto;
    padding-top: 12px;
}

.read-more {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #00930c;
    font-size: 0.82rem;
    font-weight: 600;
    transition:
        gap 0.2s,
        color 0.2s;
}

.read-more:hover {
    color: #006b09;
    gap: 7px;
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 400px) {
    .featured-title {
        font-size: 0.9rem;
    }
    .list-thumb-wrap {
        width: 68px;
        height: 58px;
    }
    .list-title {
        font-size: 0.8rem;
    }
}

@media (min-width: 1024px) {
    .featured-img-wrap {
        aspect-ratio: 16 / 9;
    }
}
</style>
