<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "ziggy-js";
import SelectInput from "@/Shared/SelectInput.vue";

const props = defineProps({
    divisions: { type: Array, default: () => [] },
    sarades: { type: Object, default: () => null },
    bivag: { type: Object, default: () => null },
    allNews: { type: Array, default: () => [] },
});

// ==================== TABS ====================
const activeTab = ref("sarades");

const tabs = computed(() => [
    { id: "sarades", name: "সারাদেশ" },
    ...(props.bivag?.subcategories || []).map((s) => ({
        id: s.id,
        name: s.name,
    })),
]);

const filteredNews = computed(() => {
    if (!props.allNews?.length) return [];

    if (activeTab.value === "sarades") {
        return props.allNews
            .filter((n) => n.category_id === props.sarades?.id)
            .slice(0, 10);
    }

    return props.allNews
        .filter((n) => n.subcategory_id === Number(activeTab.value))
        .slice(0, 10);
});

// Display grid (fallback to allNews if filter is empty)
const displayNews = computed(() => {
    const filtered = filteredNews.value;
    return (filtered.length ? filtered : props.allNews).slice(0, 6);
});

// ==================== IMAGE HELPER ====================
function imgUrl(path, fallback = "/images/default.jpg") {
    if (!path || !String(path).trim()) return fallback;
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    if (p.startsWith("/")) return p;
    return `/storage/${p}`;
}

function truncate(text, words = 10) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}

// ==================== PRAYER TIMES ====================
const countdown = ref("");
const prayerTimes = ref([]);
const dateText = ref("");
const sunrise = ref("");
const sunset = ref("");
const nextPrayer = ref("");
const nextPrayerKey = ref("");
const loadingPrayer = ref(true);
let countdownInterval = null;

const prayerNameMap = {
    Fajr: "ফজর",
    Sunrise: "সূর্যোদয়",
    Dhuhr: "জোহর",
    Asr: "আসর",
    Maghrib: "মাগরিব",
    Isha: "ইশা",
};

function getNextPrayer(timings) {
    const now = new Date();
    const timeKeys = ["Fajr", "Dhuhr", "Asr", "Maghrib", "Isha"];
    for (const key of timeKeys) {
        const [h, m] = timings[key].split(":").map(Number);
        const prayerTime = new Date();
        prayerTime.setHours(h, m, 0, 0);
        if (prayerTime > now) return key;
    }
    return "Fajr";
}

function startCountdown(nextKey, timings) {
    if (countdownInterval) clearInterval(countdownInterval);

    countdownInterval = setInterval(() => {
        const now = new Date();
        const [h, m] = timings[nextKey].split(":").map(Number);

        const prayerTime = new Date();
        prayerTime.setHours(h, m, 0, 0);
        if (prayerTime < now) prayerTime.setDate(prayerTime.getDate() + 1);

        const diff = prayerTime - now;
        if (diff <= 0) {
            countdown.value = "00:00:00";
        } else {
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            countdown.value = `${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}:${String(seconds).padStart(2, "0")}`;
        }
    }, 1000);
}

async function fetchPrayerTimes() {
    try {
        const response = await axios.get("/prayer-times");
        const data = response.data.data;
        const timings = data.timings || {};

        dateText.value = data.date?.readable || "নির্ধারিত নেই";
        sunrise.value = timings.Sunrise || "—";
        sunset.value = timings.Sunset || "—";

        const nextKey = getNextPrayer(timings);
        nextPrayerKey.value = nextKey;

        nextPrayer.value = prayerNameMap[nextKey] || nextKey;

        startCountdown(nextKey, timings);

        prayerTimes.value = [
            {
                key: "Fajr",
                name: "ফজর",
                part: "ভোর",
                time: timings.Fajr || "—",
            },
            {
                key: "Dhuhr",
                name: "জোহর",
                part: "বেলা",
                time: timings.Dhuhr || "—",
            },
            {
                key: "Asr",
                name: "আসর",
                part: "বিকেল",
                time: timings.Asr || "—",
            },
            {
                key: "Maghrib",
                name: "মাগরিব",
                part: "সন্ধ্যা",
                time: timings.Maghrib || "—",
            },
            {
                key: "Isha",
                name: "ইশা",
                part: "রাত",
                time: timings.Isha || "—",
            },
        ];
    } catch (error) {
        console.error("Failed to fetch prayer times:", error);
    } finally {
        loadingPrayer.value = false;
    }
}

onMounted(fetchPrayerTimes);
onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
});

// ==================== LOCATION FILTER ====================
const form = useForm({
    division: "",
    district: "",
    upazila: "",
});

const districts = ref([]);
const upazilas = ref([]);

watch(
    () => form.division,
    async (divisionId) => {
        form.district = "";
        form.upazila = "";
        upazilas.value = [];
        if (divisionId) {
            try {
                const res = await axios.get(`/districts/${divisionId}`);
                districts.value = res.data;
            } catch (e) {
                console.error(e);
            }
        } else {
            districts.value = [];
        }
    },
);

watch(
    () => form.district,
    async (districtId) => {
        form.upazila = "";
        if (districtId) {
            try {
                const res = await axios.get(`/upazilas/${districtId}`);
                upazilas.value = res.data;
            } catch (e) {
                console.error(e);
            }
        } else {
            upazilas.value = [];
        }
    },
);
</script>

<template>
    <section class="district-section py-6 sm:py-8">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
                <!-- ==================================================
                     LEFT: NEWS + TABS (2/3 on desktop)
                =================================================== -->
                <div class="lg:col-span-2">
                    <!-- Tabs -->
                    <div class="tabs-card">
                        <div class="tabs-scroll" role="tablist">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                class="tab-pill"
                                :class="{ active: activeTab === tab.id }"
                                role="tab"
                                :aria-selected="activeTab === tab.id"
                                @click="activeTab = tab.id"
                            >
                                {{ tab.name }}
                            </button>
                        </div>
                    </div>

                    <!-- News grid -->
                    <div v-if="displayNews.length" class="news-grid">
                        <Link
                            v-for="item in displayNews"
                            :key="item.id"
                            :href="route('news.show', item.slug)"
                            class="news-card"
                        >
                            <div class="news-img-wrap">
                                <img
                                    class="news-img"
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
                            <h3 class="news-title">
                                {{ truncate(item.title, 10) }}
                            </h3>
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="news-empty">
                        <i class="fa-regular fa-newspaper"></i>
                        <p>কোনো সংবাদ পাওয়া যায়নি</p>
                    </div>
                </div>

                <!-- ==================================================
                     RIGHT: MAP + FILTER + PRAYER (1/3 on desktop)
                =================================================== -->
                <aside class="lg:col-span-1 space-y-4">
                    <!-- ==================== FILTER CARD ==================== -->
                    <div class="filter-card">
                        <div class="filter-header">
                            <i class="fa-solid fa-location-dot"></i>
                            এক ক্লিকে বিভাগের খবর
                        </div>

                        <div class="filter-body">
                            <div class="filter-grid">
                                <SelectInput
                                    class="text-slate-800"
                                    v-model="form.division"
                                    :options="
                                        divisions.map((d) => ({
                                            label: d.bn_name,
                                            value: d.id,
                                        }))
                                    "
                                    placeholder="বিভাগ"
                                />

                                <SelectInput
                                    class="text-slate-800"
                                    v-model="form.district"
                                    :options="
                                        districts.map((d) => ({
                                            label: d.bn_name,
                                            value: d.id,
                                        }))
                                    "
                                    placeholder="জেলা"
                                />

                                <SelectInput
                                    class="text-slate-800 sm:col-span-2"
                                    v-model="form.upazila"
                                    :options="
                                        upazilas.map((u) => ({
                                            label: u.bn_name,
                                            value: u.id,
                                        }))
                                    "
                                    placeholder="উপজেলা"
                                />
                            </div>

                            <button type="button" class="search-btn">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                অনুসন্ধান করুন
                            </button>
                        </div>
                    </div>

                    <!-- ==================== PRAYER TIMES ==================== -->
                    <div class="prayer-card">
                        <div class="prayer-bg"></div>

                        <div class="prayer-content">
                            <h2 class="prayer-heading">
                                <i class="fa-solid fa-mosque"></i>
                                আজকের নামাজের সময়সূচি
                            </h2>

                            <p class="prayer-date">{{ dateText }}</p>

                            <!-- Loading skeleton -->
                            <div v-if="loadingPrayer" class="prayer-loading">
                                <div
                                    v-for="n in 5"
                                    :key="n"
                                    class="prayer-skeleton"
                                ></div>
                            </div>

                            <!-- Table -->
                            <div v-else class="prayer-table">
                                <div
                                    v-for="prayer in prayerTimes"
                                    :key="prayer.key"
                                    class="prayer-row"
                                    :class="{
                                        active: prayer.key === nextPrayerKey,
                                    }"
                                >
                                    <span class="prayer-name">
                                        {{ prayer.name }}
                                    </span>
                                    <span class="prayer-part">
                                        {{ prayer.part }}
                                    </span>
                                    <span class="prayer-time">
                                        {{ prayer.time }}
                                    </span>
                                </div>
                            </div>

                            <!-- Sun info -->
                            <div class="sun-info">
                                <div class="sun-item">
                                    <i class="fa-solid fa-sun sun-rise"></i>
                                    <span>সূর্যোদয়</span>
                                    <strong>{{ sunrise }}</strong>
                                </div>
                                <div class="sun-item">
                                    <i class="fa-solid fa-moon sun-set"></i>
                                    <span>সূর্যাস্ত</span>
                                    <strong>{{ sunset }}</strong>
                                </div>
                            </div>

                            <!-- Countdown -->
                            <div class="countdown">
                                <p class="countdown-label">পরবর্তী নামাজ</p>
                                <p class="countdown-prayer">
                                    {{ nextPrayer }}
                                </p>
                                <p class="countdown-time">
                                    {{ countdown }}
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* ==================== SECTION ==================== */
.district-section {
    background: #f1f5f9;
    font-family: "Hind Siliguri", "Roboto", sans-serif;
}

/* ==================== TABS ==================== */
.tabs-card {
    background: #ffffff;
    border-radius: 14px;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
    padding: 8px;
    margin-bottom: 14px;
}

.tabs-scroll {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 2px;
}

.tabs-scroll::-webkit-scrollbar {
    display: none;
}

.tab-pill {
    flex-shrink: 0;
    padding: 8px 16px;
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s,
        box-shadow 0.2s;
    font-family: inherit;
    white-space: nowrap;
}

.tab-pill:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.tab-pill.active {
    background: linear-gradient(135deg, #112e64, #3e6bb9);
    color: #fff;
    box-shadow: 0 4px 12px rgba(17, 46, 100, 0.3);
}

@media (min-width: 640px) {
    .tab-pill {
        font-size: 0.88rem;
        padding: 9px 20px;
    }
}

/* ==================== NEWS GRID ==================== */
.news-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

@media (min-width: 640px) {
    .news-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
}

.news-card {
    display: block;
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    text-decoration: none;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 4px 12px rgba(0, 0, 0, 0.04);
    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.news-card:hover {
    transform: translateY(-3px);
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.06),
        0 12px 24px rgba(17, 46, 100, 0.12);
}

.news-img-wrap {
    width: 100%;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
}

.news-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}

.news-card:hover .news-img {
    transform: scale(1.06);
}

.news-title {
    padding: 10px;
    margin: 0;
    color: #1e293b;
    font-size: 0.82rem;
    line-height: 1.7;
    font-weight: 600;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s;
}

.news-card:hover .news-title {
    color: #112e64;
}

@media (min-width: 640px) {
    .news-title {
        font-size: 0.88rem;
        padding: 12px;
    }
}

/* Empty */
.news-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 40px 16px;
    background: #ffffff;
    border-radius: 14px;
    color: #94a3b8;
    border: 1px dashed #e2e8f0;
}

.news-empty i {
    font-size: 30px;
    opacity: 0.5;
}

.news-empty p {
    font-size: 0.85rem;
    margin: 0;
}

/* ==================== FILTER CARD ==================== */
.filter-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
}

.filter-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #112e64, #3e6bb9);
    color: #fff;
    font-weight: 700;
    font-size: 0.92rem;
    letter-spacing: 0.2px;
}

.filter-header i {
    font-size: 0.85rem;
    opacity: 0.9;
}

.filter-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

@media (max-width: 400px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}

.search-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    font-weight: 700;
    font-size: 0.88rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.25);
    font-family: inherit;
}

.search-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 147, 12, 0.35);
}

.search-btn:active {
    transform: translateY(0);
}

.search-btn i {
    font-size: 0.85rem;
}

/* ==================== PRAYER CARD ==================== */
.prayer-card {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.08);
    isolation: isolate;
}

.prayer-bg {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(
            135deg,
            rgba(255, 255, 255, 0.94),
            rgba(240, 253, 244, 0.94)
        ),
        url("/storage/images/bg-mosque.jpeg") center/cover no-repeat;
    z-index: -1;
}

.prayer-content {
    padding: 16px;
    position: relative;
}

.prayer-heading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #006b09;
    font-weight: 700;
    font-size: 1rem;
    margin: 0 0 4px;
    letter-spacing: 0.2px;
}

.prayer-heading i {
    color: #00930c;
    font-size: 0.95rem;
}

@media (min-width: 640px) {
    .prayer-heading {
        font-size: 1.08rem;
    }
}

.prayer-date {
    text-align: center;
    color: #64748b;
    font-size: 0.78rem;
    margin: 0 0 12px;
}

/* Loading skeleton */
.prayer-loading {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
}

.prayer-skeleton {
    height: 38px;
    background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 50%, #f1f5f9 100%);
    background-size: 200% 100%;
    border-radius: 8px;
    animation: shimmer 1.4s linear infinite;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Prayer table */
.prayer-table {
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 10px;
    padding: 6px;
    border: 1px solid #d1fae5;
}

.prayer-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    align-items: center;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 0.82rem;
    color: #334155;
    transition: background 0.2s;
    border: 1px solid transparent;
}

.prayer-row.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.25);
    border-color: transparent;
}

.prayer-name {
    font-weight: 600;
}

.prayer-part {
    font-size: 0.75rem;
    opacity: 0.8;
    text-align: center;
}

.prayer-time {
    font-weight: 600;
    text-align: right;
    font-variant-numeric: tabular-nums;
}

@media (min-width: 640px) {
    .prayer-row {
        font-size: 0.86rem;
        padding: 9px 12px;
    }
}

/* Sun info */
.sun-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 12px;
}

.sun-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 8px;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    font-size: 0.72rem;
    color: #475569;
}

.sun-item i {
    font-size: 14px;
    margin-bottom: 2px;
}

.sun-rise {
    color: #f59e0b;
}

.sun-set {
    color: #6366f1;
}

.sun-item strong {
    color: #dc2626;
    font-size: 0.82rem;
    font-variant-numeric: tabular-nums;
}

/* Countdown */
.countdown {
    margin-top: 12px;
    padding: 12px;
    background: linear-gradient(135deg, #064e3b, #065f46);
    border-radius: 10px;
    text-align: center;
    color: #fff;
    box-shadow: 0 6px 16px rgba(6, 78, 59, 0.25);
}

.countdown-label {
    font-size: 0.72rem;
    opacity: 0.8;
    margin: 0 0 2px;
    letter-spacing: 0.4px;
}

.countdown-prayer {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 4px;
}

.countdown-time {
    font-size: 1.35rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: 1px;
    color: #8aff90;
    margin: 0;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
}

@media (min-width: 640px) {
    .countdown-time {
        font-size: 1.55rem;
    }
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 360px) {
    .tab-pill {
        font-size: 0.76rem;
        padding: 7px 12px;
    }
    .news-title {
        font-size: 0.78rem;
        padding: 8px;
    }
    .filter-header {
        font-size: 0.85rem;
        padding: 10px 12px;
    }
    .prayer-heading {
        font-size: 0.92rem;
    }
    .prayer-row {
        font-size: 0.76rem;
        padding: 7px 8px;
    }
    .countdown-time {
        font-size: 1.2rem;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .news-grid {
        gap: 16px;
    }
    .news-title {
        font-size: 0.92rem;
        padding: 14px;
    }
    .prayer-row {
        font-size: 0.9rem;
    }
    .countdown-time {
        font-size: 1.7rem;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .news-card,
    .news-img,
    .search-btn,
    .tab-pill,
    .prayer-skeleton {
        transition: none;
        animation: none;
    }
    .news-card:hover {
        transform: none;
    }
    .news-card:hover .news-img {
        transform: none;
    }
    .search-btn:hover {
        transform: none;
    }
}
</style>
