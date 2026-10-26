<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";

const page = usePage();

// Filter valid items (defensive)
const breakingNews = computed(() =>
    (page.props.breakingNews || []).filter(
        (item) => item && item.title && item.slug,
    ),
);

// If fewer than 2 items, still scroll nicely by duplicating
const shouldScroll = computed(() => breakingNews.value.length > 0);

// Total items determine duration so speed feels consistent
const duration = computed(() => {
    const count = breakingNews.value.length || 1;
    // Base 20s per item, min 30s, max 120s
    return Math.min(Math.max(count * 12, 30), 120);
});

const siteLogo = computed(() => page.props.site?.logo || null);
</script>

<template>
    <div class="breaking-bar-wrap">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="breaking-bar">
                <!-- ============ LABEL ============ -->
                <div class="breaking-label">
                    <span class="label-dot" aria-hidden="true"></span>
                    <span class="label-text">শিরোনাম</span>
                </div>

                <!-- ============ MARQUEE ============ -->
                <div v-if="shouldScroll" class="marquee">
                    <div
                        class="marquee-track"
                        :style="{ animationDuration: duration + 's' }"
                    >
                        <!-- First copy -->
                        <div class="marquee-content">
                            <Link
                                v-for="item in breakingNews"
                                :key="'a-' + item.id"
                                :href="route('news.details', item.slug)"
                                class="news-link"
                            >
                                <span class="news-text">{{ item.title }}</span>
                                <img
                                    v-if="siteLogo"
                                    :src="`/storage/${siteLogo}`"
                                    alt=""
                                    class="news-bullet"
                                    aria-hidden="true"
                                />
                                <i
                                    v-else
                                    class="fa-solid fa-circle news-bullet-icon"
                                    aria-hidden="true"
                                ></i>
                            </Link>
                        </div>

                        <!-- Duplicate for seamless loop -->
                        <div class="marquee-content" aria-hidden="true">
                            <Link
                                v-for="item in breakingNews"
                                :key="'b-' + item.id"
                                :href="route('news.details', item.slug)"
                                class="news-link"
                                tabindex="-1"
                            >
                                <span class="news-text">{{ item.title }}</span>
                                <img
                                    v-if="siteLogo"
                                    :src="`/storage/${siteLogo}`"
                                    alt=""
                                    class="news-bullet"
                                />
                                <i
                                    v-else
                                    class="fa-solid fa-circle news-bullet-icon"
                                ></i>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="empty-state">
                    <span>কোনো ব্রেকিং নিউজ নেই</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ==================== OUTER BAR ==================== */
.breaking-bar-wrap {
    padding: 12px 0;
    background: #f8fafc;
}

.breaking-bar {
    display: flex;
    align-items: stretch;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 6px 20px rgba(0, 147, 12, 0.06);
    border: 1px solid #e2e8f0;
    min-height: 44px;
}

/* ==================== LABEL (LEFT) ==================== */
.breaking-label {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: linear-gradient(135deg, #00930c 0%, #00b515 100%);
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.3px;
    white-space: nowrap;
    flex-shrink: 0;
    position: relative;
    z-index: 2;
    box-shadow: 2px 0 8px rgba(0, 147, 12, 0.2);
}

/* Pulsing red dot — breaking news vibe */
.label-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.8);
    animation: pulse 1.6s infinite;
    flex-shrink: 0;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.9);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(255, 255, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
    }
}

.label-text {
    line-height: 1;
}

/* Small chevron/notch pointing right on the label */
.breaking-label::after {
    content: "";
    position: absolute;
    right: -10px;
    top: 0;
    bottom: 0;
    width: 0;
    height: 0;
    border-top: 22px solid transparent;
    border-bottom: 22px solid transparent;
    border-left: 10px solid #00b515;
    pointer-events: none;
}

@media (min-width: 640px) {
    .breaking-label {
        padding: 12px 18px;
        font-size: 0.9rem;
    }
    .breaking-label::after {
        border-top-width: 24px;
        border-bottom-width: 24px;
    }
}

@media (min-width: 1024px) {
    .breaking-label {
        padding: 14px 22px;
        font-size: 0.95rem;
    }
    .breaking-label::after {
        border-top-width: 26px;
        border-bottom-width: 26px;
    }
}

/* ==================== MARQUEE ==================== */
.marquee {
    position: relative;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding-left: 16px;

    /* Fade masks left/right for smooth edge */
    -webkit-mask-image: linear-gradient(
        to right,
        transparent 0,
        #000 24px,
        #000 calc(100% - 24px),
        transparent 100%
    );
    mask-image: linear-gradient(
        to right,
        transparent 0,
        #000 24px,
        #000 calc(100% - 24px),
        transparent 100%
    );
}

.marquee-track {
    display: flex;
    align-items: center;
    width: max-content;
    animation: marquee 60s linear infinite;
    will-change: transform;
}

.marquee-content {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

/* ==================== NEWS LINK ==================== */
.news-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: #1e293b;
    font-size: 0.88rem;
    font-weight: 500;
    line-height: 1.4;
    white-space: nowrap;
    text-decoration: none;
    transition: color 0.2s ease;
    position: relative;
}

.news-link::after {
    content: "";
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    height: 60%;
    width: 1px;
    background: linear-gradient(to bottom, transparent, #cbd5e1, transparent);
}

.news-link:hover {
    color: #00930c;
}

.news-text {
    transition: transform 0.2s ease;
}

.news-link:hover .news-text {
    transform: translateY(-1px);
}

/* Logo bullet between items */
.news-bullet {
    width: 14px;
    height: 14px;
    object-fit: contain;
    border-radius: 50%;
    opacity: 0.85;
    flex-shrink: 0;
}

/* Fallback dot if no logo */
.news-bullet-icon {
    color: #00930c;
    font-size: 6px;
    flex-shrink: 0;
    opacity: 0.7;
}

@media (min-width: 640px) {
    .news-link {
        font-size: 0.92rem;
        padding: 12px 26px;
    }
    .news-bullet {
        width: 16px;
        height: 16px;
    }
}

@media (min-width: 1024px) {
    .news-link {
        font-size: 0.95rem;
        padding: 14px 32px;
    }
    .news-bullet {
        width: 18px;
        height: 18px;
    }
}

/* Pause on hover */
.marquee:hover .marquee-track {
    animation-play-state: paused;
}

/* ==================== EMPTY STATE ==================== */
.empty-state {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 0.88rem;
    padding: 12px 20px;
}

/* ==================== ANIMATION ==================== */
@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .marquee-track {
        animation: none;
    }
    .label-dot {
        animation: none;
    }
    .marquee {
        overflow-x: auto;
    }
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 400px) {
    .breaking-label {
        padding: 10px 10px;
        font-size: 0.78rem;
        gap: 6px;
    }
    .breaking-label::after {
        border-top-width: 22px;
        border-bottom-width: 22px;
        border-left-width: 8px;
        right: -8px;
    }
    .news-link {
        font-size: 0.82rem;
        padding: 10px 16px;
        gap: 8px;
    }
    .label-dot {
        width: 6px;
        height: 6px;
    }
}
</style>
