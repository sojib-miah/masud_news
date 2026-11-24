<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { ref, computed, watch } from "vue";

const props = defineProps({
    videos: { type: Object, required: true },
});

// ── Modal state ──
const active = ref(null);

function openVideo(v) {
    active.value = v;
    document.body.style.overflow = "hidden";
}
function closeVideo() {
    active.value = null;
    document.body.style.overflow = "";
}
watch(active, (v) => {
    if (!v) document.body.style.overflow = "";
});

// ── Helpers ──
function extractYoutubeId(url) {
    if (!url) return null;
    const m = String(url).match(
        /(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/,
    );
    return m ? m[1] : null;
}
function thumb(url) {
    const id = extractYoutubeId(url);
    return id
        ? `https://i.ytimg.com/vi/${id}/hqdefault.jpg`
        : "/images/default-video.jpg";
}
const embedUrl = computed(() => {
    const id = extractYoutubeId(active.value?.link);
    return id ? `https://www.youtube.com/embed/${id}?autoplay=1&rel=0` : null;
});
</script>

<template>
    <Head title="Video Gallery" />
    <Layout>
        <section class="video-page py-8">
            <div class="container mx-auto px-3 sm:px-4 lg:px-6">
                <!-- Page header -->
                <div class="page-head">
                    <h1 class="page-title">
                        <i class="fa-solid fa-circle-play"></i>
                        ভিডিও গ্যালারি
                    </h1>
                    <p class="page-sub">
                        সর্বশেষ ভিডিও সংবাদ ও প্রতিবেদন দেখুন।
                    </p>
                </div>

                <!-- Grid -->
                <div
                    v-if="videos.data && videos.data.length"
                    class="video-grid"
                >
                    <article
                        v-for="v in videos.data"
                        :key="v.id"
                        class="video-card"
                        @click="openVideo(v)"
                    >
                        <div class="thumb-wrap">
                            <img
                                :src="thumb(v.link)"
                                :alt="'Video ' + v.id"
                                loading="lazy"
                                @error="
                                    (e) =>
                                        (e.target.src =
                                            '/images/default-video.jpg')
                                "
                            />
                            <span class="play-btn" aria-hidden="true">
                                <i class="fa-solid fa-play"></i>
                            </span>
                        </div>
                        <div class="card-foot">
                            <span class="card-label">
                                <i class="fa-brands fa-youtube"></i>
                                ভিডিও
                            </span>
                        </div>
                    </article>
                </div>

                <!-- Empty -->
                <div v-else class="empty">
                    <i class="fa-regular fa-circle-play"></i>
                    <p>কোনো ভিডিও পাওয়া যায়নি।</p>
                </div>

                <!-- Pagination -->
                <nav
                    v-if="videos.last_page > 1"
                    class="pager"
                    aria-label="Pagination"
                >
                    <template v-for="(link, i) in videos.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            class="pager-btn"
                            :class="{ active: link.active }"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="pager-btn disabled"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </section>

        <!-- ==================== VIDEO MODAL ==================== -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="active && embedUrl"
                    class="modal-wrap"
                    @click.self="closeVideo"
                >
                    <button class="modal-close" @click="closeVideo">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <div class="modal-frame">
                        <iframe
                            :src="embedUrl"
                            title="Video player"
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
                        ></iframe>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </Layout>
</template>

<style scoped>
.page-head {
    margin-bottom: 20px;
}
.page-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.4rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.page-title i {
    color: #e11d48;
}
.page-sub {
    color: #64748b;
    font-size: 0.88rem;
    margin-top: 15px;
}
@media (min-width: 640px) {
    .page-title {
        font-size: 1.75rem;
    }
    .page-sub {
        font-size: 0.95rem;
    }
}

/* ---------- GRID ---------- */
.video-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 12px;
}
@media (min-width: 480px) {
    .video-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
}
@media (min-width: 768px) {
    .video-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }
}
@media (min-width: 1024px) {
    .video-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }
}

/* ---------- CARD ---------- */
.video-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.05),
        0 4px 14px rgba(15, 23, 42, 0.06);
    cursor: pointer;
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
    display: flex;
    flex-direction: column;
}
.video-card:hover {
    transform: translateY(-3px);
    box-shadow:
        0 4px 8px rgba(15, 23, 42, 0.08),
        0 12px 28px rgba(15, 23, 42, 0.12);
}

.thumb-wrap {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%; /* 16:9 */
    background: #0f172a;
    overflow: hidden;
}
.thumb-wrap img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}
.video-card:hover .thumb-wrap img {
    transform: scale(1.05);
}

.play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e11d48, #be123c);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 6px 20px rgba(225, 29, 72, 0.5);
    transition:
        transform 0.25s,
        box-shadow 0.25s;
}
.video-card:hover .play-btn {
    transform: translate(-50%, -50%) scale(1.1);
    box-shadow: 0 8px 28px rgba(225, 29, 72, 0.65);
}
@media (min-width: 1024px) {
    .play-btn {
        width: 58px;
        height: 58px;
        font-size: 20px;
    }
}

.card-foot {
    padding: 10px 12px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}
.card-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.76rem;
    font-weight: 700;
    color: #e11d48;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

/* ---------- EMPTY ---------- */
.empty {
    background: #fff;
    border-radius: 12px;
    padding: 60px 20px;
    text-align: center;
    color: #94a3b8;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.empty i {
    font-size: 44px;
    opacity: 0.5;
}

/* ---------- PAGER ---------- */
.pager {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 6px;
    margin-top: 24px;
}
.pager-btn {
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #fff;
    color: #334155;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition:
        background 0.15s,
        color 0.15s,
        border-color 0.15s;
}
.pager-btn:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #00930c;
}
.pager-btn.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    border-color: #00930c;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.28);
}
.pager-btn.disabled {
    opacity: 0.45;
    cursor: not-allowed;
    background: #f8fafc;
}

/* ---------- MODAL ---------- */
.modal-wrap {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.modal-close:hover {
    background: rgba(255, 255, 255, 0.25);
}
.modal-frame {
    position: relative;
    width: 100%;
    max-width: 1000px;
    aspect-ratio: 16 / 9;
    background: #000;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6);
}
.modal-frame iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* ---------- REDUCED MOTION ---------- */
@media (prefers-reduced-motion: reduce) {
    .video-card,
    .thumb-wrap img,
    .play-btn {
        transition: none;
    }
}
</style>
