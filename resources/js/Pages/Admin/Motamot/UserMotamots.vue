<script setup>
import { Head, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import { route } from "ziggy-js";

const props = defineProps({
    user: Object,
    motamots: { type: Array, default: () => [] },
});

// ==================== DELETE ====================
function deleteMotamot(motamot) {
    if (confirm("Are you sure you want to delete this motamot?")) {
        router.delete(route("motamot.destroy", motamot.id), {
            preserveScroll: true,
        });
    }
}

// ==================== IMAGE LIGHTBOX ====================
const lightboxOpen = ref(false);
const lightboxImages = ref([]);
const lightboxIndex = ref(0);

function openLightbox(images, index = 0) {
    lightboxImages.value = images;
    lightboxIndex.value = index;
    lightboxOpen.value = true;
    document.body.style.overflow = "hidden";
}

function closeLightbox() {
    lightboxOpen.value = false;
    lightboxImages.value = [];
    lightboxIndex.value = 0;
    document.body.style.overflow = "";
}

function nextImage() {
    if (!lightboxImages.value.length) return;
    lightboxIndex.value =
        (lightboxIndex.value + 1) % lightboxImages.value.length;
}

function prevImage() {
    if (!lightboxImages.value.length) return;
    lightboxIndex.value =
        (lightboxIndex.value - 1 + lightboxImages.value.length) %
        lightboxImages.value.length;
}

// Keyboard nav
function onKeydown(e) {
    if (!lightboxOpen.value) return;
    if (e.key === "Escape") closeLightbox();
    if (e.key === "ArrowRight") nextImage();
    if (e.key === "ArrowLeft") prevImage();
}

if (typeof window !== "undefined") {
    window.addEventListener("keydown", onKeydown);
}

// ==================== HELPERS ====================
function initials(name) {
    if (!name) return "?";
    return name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase();
}

function formatDate(d) {
    if (!d) return "—";
    try {
        return new Date(d).toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    } catch {
        return d;
    }
}

function formatTime(d) {
    if (!d) return "";
    try {
        return new Date(d).toLocaleTimeString("en-GB", {
            hour: "2-digit",
            minute: "2-digit",
        });
    } catch {
        return "";
    }
}

const totalImages = computed(() =>
    props.motamots.reduce((sum, m) => sum + (m.images?.length ?? 0), 0),
);
</script>

<template>
    <Head title="User Motamots" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <Breadcrumb />

        <!-- ==================== PAGE HEADER ==================== -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
        >
            <div
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-rose-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-rose-600 text-white font-black text-lg shadow-lg ring-2 ring-white/20"
                    >
                        {{ initials(props.user?.name) }}
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-amber-400 text-xs font-semibold uppercase tracking-wider mb-1"
                        >
                            <i class="fa-solid fa-comment-dots mr-1"></i>
                            Motamots
                        </p>
                        <h1
                            class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white tracking-tight truncate"
                        >
                            {{ props.user?.name || "User" }}
                        </h1>
                        <p class="text-slate-300 text-sm mt-0.5 truncate">
                            {{ props.user?.email || "" }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i class="fa-solid fa-comments text-amber-300"></i>
                        {{ props.motamots?.length ?? 0 }} Motamots
                    </div>
                    <div
                        v-if="totalImages"
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold"
                    >
                        <i class="fa-solid fa-images text-rose-300"></i>
                        {{ totalImages }} Images
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== EMPTY ==================== -->
        <div
            v-if="!props.motamots?.length"
            class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md py-16 px-6 text-center"
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
            >
                <i
                    class="fa-solid fa-comment-slash text-3xl text-slate-400"
                ></i>
            </div>
            <p class="text-lg font-bold text-slate-700 mb-1">No motamots yet</p>
            <p class="text-sm text-slate-500">
                This user hasn't submitted any motamots.
            </p>
        </div>

        <!-- ==================== MOTAMOT LIST ==================== -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div
                v-for="(motamot, index) in props.motamots"
                :key="motamot.id"
                class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all overflow-hidden flex flex-col"
            >
                <!-- Card header -->
                <div
                    class="flex items-start gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 sm:px-5 py-3"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white font-black text-sm shadow"
                    >
                        {{ index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800 leading-snug line-clamp-2"
                        >
                            {{ motamot.subject || "Untitled" }}
                        </h2>
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-[11px] text-slate-500"
                        >
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-regular fa-calendar"></i>
                                {{ formatDate(motamot.created_at) }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-regular fa-clock"></i>
                                {{ formatTime(motamot.created_at) }}
                            </span>
                            <span
                                v-if="motamot.images?.length"
                                class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 font-bold text-amber-700 ring-1 ring-amber-200"
                            >
                                <i class="fa-solid fa-images text-[9px]"></i>
                                {{ motamot.images.length }}
                            </span>
                        </div>
                    </div>

                    <button
                        @click="deleteMotamot(motamot)"
                        class="shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100 hover:scale-110 transition"
                        title="Delete"
                    >
                        <i class="fa fa-trash text-xs"></i>
                    </button>
                </div>

                <!-- Card body -->
                <div class="p-4 sm:p-5 flex-1 flex flex-col gap-3">
                    <!-- Description -->
                    <div
                        v-if="motamot.description"
                        class="prose prose-sm max-w-none text-sm text-slate-700 leading-relaxed rounded-lg bg-slate-50 ring-1 ring-slate-100 p-3 line-clamp-4"
                        v-html="motamot.description"
                    ></div>
                    <p v-else class="text-sm text-slate-400 italic">
                        No description provided
                    </p>

                    <!-- Images -->
                    <div
                        v-if="motamot.images && motamot.images.length"
                        class="mt-1"
                    >
                        <div
                            class="grid gap-2"
                            :class="
                                motamot.images.length === 1
                                    ? 'grid-cols-1'
                                    : motamot.images.length === 2
                                      ? 'grid-cols-2'
                                      : 'grid-cols-2 sm:grid-cols-3'
                            "
                        >
                            <button
                                v-for="(img, i) in motamot.images"
                                :key="i"
                                type="button"
                                @click="openLightbox(motamot.images, i)"
                                class="group relative aspect-square rounded-xl overflow-hidden ring-1 ring-slate-200 bg-slate-100 hover:ring-2 hover:ring-amber-400 transition"
                            >
                                <img
                                    :src="`/storage/${img}`"
                                    alt="Motamot image"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                    loading="lazy"
                                />
                                <!-- Overlay on hover -->
                                <div
                                    class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center"
                                >
                                    <i
                                        class="fa-solid fa-magnifying-glass-plus text-white text-xl drop-shadow"
                                    ></i>
                                </div>
                                <!-- Image count badge (first image only) -->
                                <span
                                    v-if="i === 2 && motamot.images.length > 3"
                                    class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-xl font-black"
                                >
                                    +{{ motamot.images.length - 3 }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>

    <!-- ============================================= -->
    <!-- ================= LIGHTBOX ================== -->
    <!-- ============================================= -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="lightboxOpen && lightboxImages.length"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
        >
            <!-- Backdrop -->
            <div
                @click="closeLightbox"
                class="absolute inset-0 bg-slate-950/90 backdrop-blur-sm"
            ></div>

            <!-- Close button -->
            <button
                @click="closeLightbox"
                class="absolute top-4 right-4 z-20 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-red-500 transition ring-1 ring-white/20 backdrop-blur"
                title="Close (Esc)"
            >
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            <!-- Counter -->
            <div
                class="absolute top-4 left-4 z-20 inline-flex items-center gap-2 rounded-full bg-white/10 text-white backdrop-blur ring-1 ring-white/20 px-3 py-1.5 text-xs font-bold"
            >
                <i class="fa-solid fa-images"></i>
                {{ lightboxIndex + 1 }} / {{ lightboxImages.length }}
            </div>

            <!-- Prev / Next -->
            <button
                v-if="lightboxImages.length > 1"
                @click.stop="prevImage"
                class="absolute left-2 sm:left-6 z-20 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition ring-1 ring-white/20 backdrop-blur"
                title="Previous (←)"
            >
                <i class="fa-solid fa-chevron-left text-lg"></i>
            </button>
            <button
                v-if="lightboxImages.length > 1"
                @click.stop="nextImage"
                class="absolute right-2 sm:right-6 z-20 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 transition ring-1 ring-white/20 backdrop-blur"
                title="Next (→)"
            >
                <i class="fa-solid fa-chevron-right text-lg"></i>
            </button>

            <!-- Image -->
            <div
                class="relative z-10 max-w-[90vw] max-h-[85vh] rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10"
            >
                <img
                    :src="`/storage/${lightboxImages[lightboxIndex]}`"
                    alt="Motamot image"
                    class="max-w-full max-h-[85vh] object-contain bg-slate-900"
                />
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Rich text inside cards */
.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
