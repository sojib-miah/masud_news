<script setup>
import Layout from "@/Components/Layout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { computed, onMounted, onUnmounted, reactive, ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    news: { type: Object, required: true },
    relatedNews: { type: Array, default: () => [] },
});

const page = usePage();

// ==================== VIEW COUNT ====================
onMounted(() => {
    setTimeout(() => {
        axios
            .post(route("news.count.view"), { news_id: props.news.id })
            .then((response) => {
                props.news.current_view = response.data.current_view;
                props.news.total_view = response.data.total_view;
            })
            .catch((error) => console.log(error));
    }, 5000);
});

// ==================== COMMENTS ====================
const form = reactive({ body: "" });

function submitComment(newsId) {
    router.post(`/news/${newsId}/comments`, form, {
        preserveScroll: true,
        onSuccess: () => {
            form.body = "";
        },
    });
}

// ==================== DATE ====================
const formattedDate = computed(() => {
    const date = new Date(props.news.created_at);
    return new Intl.DateTimeFormat("bn-BD", {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
});

// ==================== REPORTER ====================
const reporterImage = computed(() => {
    const rp = props.news.user?.reporter_profile;
    if (rp?.photo) return `/storage/${rp.photo}`;
    if (props.news.user?.image) return `/storage/${props.news.user.image}`;
    return "/images/default.jpg";
});

const reporterName = computed(() => props.news.user?.name || "স্টাফ রিপোর্টার");

const reporterTypes = computed(() => {
    const types = props.news.user?.reporter_profile?.types;
    if (!types || !types.length) return "ডেস্ক রিপোর্টার";
    return types.map((t) => t.name).join(", ");
});

const reporterLocation = computed(() => {
    const rp = props.news?.user?.reporter_profile;
    if (!rp) return null;

    // -------- City Corporation path --------
    const hasCityCorp =
        rp.city_corporation ||
        rp.city_corporation_data?.name ||
        rp.ward ||
        rp.ward_data?.name;

    const hasDistrict =
        rp.district ||
        rp.district_data?.name ||
        rp.upazila ||
        rp.upazila_data?.name ||
        rp.union ||
        rp.union_data?.name;

    // If city corporation is set → use city path
    if (hasCityCorp && !hasDistrict) {
        return [
            rp.division_data?.name,
            rp.city_corporation_data?.name,
            rp.ward_data?.name,
        ]
            .filter(Boolean)
            .join(", ");
    }

    // Otherwise → use district path
    return [
        rp.division_data?.name,
        rp.district_data?.name,
        rp.upazila_data?.name,
        rp.union_data?.name,
    ]
        .filter(Boolean)
        .join(", ");
});

// ==================== TAGS ====================
const tags = computed(() => {
    try {
        return typeof props.news.tag === "string"
            ? JSON.parse(props.news.tag)
            : props.news.tag || [];
    } catch {
        return [];
    }
});

// ==================== SHARE ====================
const newsUrl = computed(() =>
    typeof window !== "undefined" ? window.location.href : "",
);
const newsTitle = computed(() => props.news.title);

const shareLinks = computed(() => ({
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(newsUrl.value)}`,
    twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(newsTitle.value)}&url=${encodeURIComponent(newsUrl.value)}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(newsUrl.value)}`,
    whatsapp: `https://api.whatsapp.com/send?text=${encodeURIComponent(newsTitle.value)}%20${encodeURIComponent(newsUrl.value)}`,
}));

// ==================== COPY / PRINT ====================
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        alert("নিউজ লিঙ্ক কপি হয়েছে");
    } catch (err) {
        alert("কপি করতে সমস্যা হয়েছে");
    }
};

const printNews = () => {
    const content = document.getElementById("printArea").innerHTML;
    const printWindow = window.open("", "", "width=800,height=600");
    printWindow.document.write(`
        <html>
        <head>
            <title>Print News</title>
            <style>
                body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
                img { max-width: 100%; height: auto; margin-bottom: 20px; border-radius: 8px; }
                h1 { font-size: 24px; margin-bottom: 20px; }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
};

// ==================== TEXT TO SPEECH ====================
const isReading = ref(false);
let utterance = null;

const speakNews = () => {
    const el = document.getElementById("news-description");
    const text = el ? el.innerText.trim() : "";
    if (!text) return alert("No text found!");

    const banglaRegex = /[\u0980-\u09FF]/;
    let finalText = text;
    if (banglaRegex.test(text)) {
        finalText = transliterateBanglaToEnglish(text);
    }

    utterance = new SpeechSynthesisUtterance(finalText);
    const voices = window.speechSynthesis.getVoices();
    const enVoice = voices.find((v) => v.lang.includes("en")) || null;
    if (enVoice) utterance.voice = enVoice;

    utterance.rate = banglaRegex.test(text) ? 0.8 : 1;
    utterance.pitch = 1;
    utterance.volume = 1;

    utterance.onstart = () => (isReading.value = true);
    utterance.onend = () => (isReading.value = false);
    utterance.onerror = () => (isReading.value = false);

    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);
};

const stopReading = () => {
    window.speechSynthesis.cancel();
    isReading.value = false;
};

onUnmounted(() => {
    window.speechSynthesis.cancel();
    if (countdownInterval) clearInterval(countdownInterval);
});

function transliterateBanglaToEnglish(text) {
    const map = {
        আ: "a",
        "া": "a",
        ই: "i",
        "ি": "i",
        উ: "u",
        "ু": "u",
        এ: "e",
        "ে": "e",
        ও: "o",
        "ো": "o",
        ক: "k",
        খ: "kh",
        গ: "g",
        ঘ: "gh",
        ঙ: "ng",
        চ: "ch",
        ছ: "chh",
        জ: "j",
        ঝ: "jh",
        ঞ: "n",
        ট: "t",
        ঠ: "th",
        ড: "d",
        ঢ: "dh",
        ণ: "n",
        ত: "t",
        থ: "th",
        দ: "d",
        ধ: "dh",
        ন: "n",
        প: "p",
        ফ: "ph",
        ব: "b",
        ভ: "bh",
        ম: "m",
        য: "y",
        র: "r",
        ল: "l",
        শ: "sh",
        ষ: "sh",
        স: "s",
        হ: "h",
        য়: "y",
        "ঁ": "n",
        "ং": "ng",
        "ঃ": "h",
        "০": "0",
        "১": "1",
        "২": "2",
        "৩": "3",
        "৪": "4",
        "৫": "5",
        "৬": "6",
        "৭": "7",
        "৮": "8",
        "৯": "9",
    };
    return text
        .split("")
        .map((c) => map[c] || c)
        .join("");
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

const getNextPrayer = (timings) => {
    const now = new Date();
    const timeKeys = ["Fajr", "Dhuhr", "Asr", "Maghrib", "Isha"];
    for (let key of timeKeys) {
        const [h, m] = timings[key].split(":").map(Number);
        const prayerTime = new Date();
        prayerTime.setHours(h, m, 0, 0);
        if (prayerTime > now) return key;
    }
    return "Fajr";
};

const startCountdown = (nextKey, timings) => {
    if (countdownInterval) clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        const now = new Date();
        const [h, m] = timings[nextKey].split(":").map(Number);
        let prayerTime = new Date();
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
};

const fetchPrayerTimes = async () => {
    try {
        const response = await axios.get("/prayer-times");
        const data = response.data.data;
        const timings = data.timings || {};

        dateText.value = data.date?.readable || "নির্ধারিত নেই";
        sunrise.value = timings.Sunrise || "—";
        sunset.value = timings.Sunset || "—";

        const nextKey = getNextPrayer(timings);
        nextPrayerKey.value = nextKey;
        nextPrayer.value = `${prayerNameMap[nextKey] || nextKey} - ${timings[nextKey] ?? "—"}`;
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
};

onMounted(fetchPrayerTimes);

// ==================== CALENDAR ====================
const months = [
    { en: "January", bn: "জানুয়ারি", index: 0 },
    { en: "February", bn: "ফেব্রুয়ারি", index: 1 },
    { en: "March", bn: "মার্চ", index: 2 },
    { en: "April", bn: "এপ্রিল", index: 3 },
    { en: "May", bn: "মে", index: 4 },
    { en: "June", bn: "জুন", index: 5 },
    { en: "July", bn: "জুলাই", index: 6 },
    { en: "August", bn: "আগস্ট", index: 7 },
    { en: "September", bn: "সেপ্টেম্বর", index: 8 },
    { en: "October", bn: "অক্টোবর", index: 9 },
    { en: "November", bn: "নভেম্বর", index: 10 },
    { en: "December", bn: "ডিসেম্বর", index: 11 },
];

const weekDays = ["রবি", "সোম", "মঙ্গল", "বুধ", "বৃহ", "শুক্র", "শনি"];
const years = Array.from({ length: 20 }, (_, i) => 2020 + i);

const today = new Date();
const todayDate = today.getDate();
const todayMonth = today.getMonth();
const todayYear = today.getFullYear();

const selectedMonth = ref(months[todayMonth]);
const selectedYear = ref(todayYear);

const calendarDays = computed(() => {
    const year = selectedYear.value;
    const monthIndex = selectedMonth.value.index;
    const firstDay = new Date(year, monthIndex, 1).getDay();
    const totalDays = new Date(year, monthIndex + 1, 0).getDate();
    const blanks = Array(firstDay).fill(null);
    const days = Array.from({ length: totalDays }, (_, i) => i + 1);
    return [...blanks, ...days];
});

const isToday = (day) =>
    day === todayDate &&
    selectedMonth.value.index === todayMonth &&
    selectedYear.value === todayYear;

const isFuture = (day) => {
    if (!day) return false;
    const date = new Date(selectedYear.value, selectedMonth.value.index, day);
    return date > new Date();
};

const goArchive = (day) => {
    const date = new Date(selectedYear.value, selectedMonth.value.index, day);
    const formatted = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
    router.get(route("news.archive"), { date: formatted });
};

// ==================== SIDEBAR ====================
const latestNews = computed(() => page.props.sidebarLatest ?? []);
const popularNews = computed(() => page.props.sidebarPopular ?? []);
const activeFilter = ref("latest");
const filteredNews = computed(() =>
    activeFilter.value === "latest" ? latestNews.value : popularNews.value,
);

function truncate(text, words = 9) {
    if (!text) return "";
    const parts = text.split(" ");
    return parts.length > words
        ? parts.slice(0, words).join(" ") + "..."
        : text;
}
</script>

<template>
    <Layout>
        <Head :title="news.title" />

        <div class="article-page py-6 sm:py-8">
            <div class="container mx-auto px-3 sm:px-4 lg:px-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
                    <!-- ==================================================
                         MAIN ARTICLE (2/3)
                    =================================================== -->
                    <main class="lg:col-span-2">
                        <article class="article-card">
                            <!-- ==================== BREADCRUMB ==================== -->
                            <nav
                                class="article-breadcrumb"
                                aria-label="Breadcrumb"
                            >
                                <Link :href="route('home')" class="crumb-link">
                                    <i class="fa-solid fa-house"></i>
                                    মূল পাতা
                                </Link>
                                <i
                                    class="fa-solid fa-angle-right crumb-sep"
                                ></i>
                                <Link
                                    :href="route('all.news', 'jateey')"
                                    class="crumb-link"
                                >
                                    জাতীয়
                                </Link>
                            </nav>

                            <!-- ==================== TITLE ==================== -->
                            <h1 class="article-title">{{ news.title }}</h1>

                            <!-- ==================== META ==================== -->
                            <div class="article-meta">
                                <div class="author-block">
                                    <img
                                        class="author-img"
                                        :src="reporterImage"
                                        :alt="reporterName"
                                        @error="
                                            (e) =>
                                                (e.target.src =
                                                    '/images/default.jpg')
                                        "
                                    />
                                    <div class="author-info">
                                        <p class="author-name">
                                            {{ reporterName }}
                                            <span class="author-types">
                                                ({{ reporterTypes }})
                                            </span>
                                        </p>

                                        <p
                                            v-if="reporterLocation"
                                            class="author-location"
                                        >
                                            <i
                                                class="fa-solid fa-location-dot"
                                            ></i>
                                            {{ reporterLocation }}
                                        </p>
                                    </div>
                                </div>

                                <div class="meta-right">
                                    <p class="meta-date">
                                        <i class="fa-regular fa-clock"></i>
                                        {{ formattedDate }}
                                    </p>
                                </div>
                            </div>

                            <!-- ==================== HERO IMAGE ==================== -->
                            <div class="article-hero">
                                <img
                                    class="article-hero-img"
                                    :src="
                                        news.image
                                            ? `/storage/${news.image}`
                                            : '/images/default.jpg'
                                    "
                                    :alt="news.title"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                />
                            </div>

                            <!-- ==================== ACTIONS ==================== -->
                            <div class="article-actions">
                                <button
                                    class="action-btn print"
                                    @click="printNews"
                                >
                                    <i class="fa-solid fa-print"></i>
                                    <span>প্রিন্ট</span>
                                </button>
                                <button
                                    class="action-btn copy"
                                    @click="copyLink"
                                >
                                    <i class="fa-regular fa-copy"></i>
                                    <span>লিঙ্ক কপি</span>
                                </button>
                                <button
                                    v-if="!isReading"
                                    class="action-btn listen"
                                    @click="speakNews"
                                >
                                    <i class="fa-solid fa-volume-high"></i>
                                    <span>শুনুন</span>
                                </button>
                                <button
                                    v-else
                                    class="action-btn stop"
                                    @click="stopReading"
                                >
                                    <i class="fa-solid fa-stop"></i>
                                    <span>বন্ধ</span>
                                </button>
                            </div>

                            <!-- ==================== DESCRIPTION ==================== -->
                            <div
                                id="news-description"
                                class="article-content prose"
                                v-html="news.description"
                            ></div>

                            <!-- ==================== APPROVAL ==================== -->
                            <div class="approval-bar">
                                <i class="fa-solid fa-circle-check"></i>
                                অনুমোদন করেছেন:
                                <strong>মাসুদ — ডেক্স রিপোর্ট</strong>
                            </div>

                            <!-- ==================== TAGS ==================== -->
                            <div v-if="tags.length" class="article-tags">
                                <span class="tags-label">ট্যাগ:</span>
                                <span
                                    v-for="tag in tags"
                                    :key="tag"
                                    class="tag-item"
                                >
                                    #{{ tag }}
                                </span>
                            </div>

                            <!-- ==================== SHARE ==================== -->
                            <div class="share-section">
                                <h2 class="share-title">
                                    সংবাদটি ভালো লাগলে শেয়ার করুন
                                </h2>
                                <div class="share-buttons">
                                    <a
                                        :href="shareLinks.facebook"
                                        target="_blank"
                                        rel="noopener"
                                        class="share-btn facebook"
                                        aria-label="Share on Facebook"
                                    >
                                        <i class="fa-brands fa-facebook-f"></i>
                                        <span>Facebook</span>
                                    </a>
                                    <a
                                        :href="shareLinks.twitter"
                                        target="_blank"
                                        rel="noopener"
                                        class="share-btn twitter"
                                        aria-label="Share on Twitter"
                                    >
                                        <i class="fa-brands fa-x-twitter"></i>
                                        <span>Twitter</span>
                                    </a>
                                    <a
                                        :href="shareLinks.linkedin"
                                        target="_blank"
                                        rel="noopener"
                                        class="share-btn linkedin"
                                        aria-label="Share on LinkedIn"
                                    >
                                        <i class="fa-brands fa-linkedin-in"></i>
                                        <span>LinkedIn</span>
                                    </a>
                                    <a
                                        :href="shareLinks.whatsapp"
                                        target="_blank"
                                        rel="noopener"
                                        class="share-btn whatsapp"
                                        aria-label="Share on WhatsApp"
                                    >
                                        <i class="fa-brands fa-whatsapp"></i>
                                        <span>WhatsApp</span>
                                    </a>
                                </div>
                            </div>

                            <!-- ==================== COMMENTS ==================== -->
                            <div class="comments-section">
                                <h2 class="comments-title">
                                    <i class="fa-regular fa-comments"></i>
                                    কমেন্ট করুন
                                </h2>

                                <form
                                    class="comment-form"
                                    @submit.prevent="submitComment(news.id)"
                                >
                                    <input
                                        v-model="form.body"
                                        type="text"
                                        placeholder="আপনার মন্তব্য লিখুন..."
                                        class="comment-input"
                                        required
                                    />
                                    <button
                                        type="submit"
                                        class="comment-submit"
                                    >
                                        <i class="fa-solid fa-paper-plane"></i>
                                        পাঠান
                                    </button>
                                </form>

                                <!-- Comment list -->
                                <div class="comment-list">
                                    <div
                                        v-if="!news.comments?.length"
                                        class="comment-empty"
                                    >
                                        <i class="fa-regular fa-comment"></i>
                                        <p>এখনো কোনো কমেন্ট নেই</p>
                                    </div>

                                    <div
                                        v-for="comment in news.comments"
                                        :key="comment.id"
                                        class="comment-item"
                                    >
                                        <div class="comment-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div class="comment-body">
                                            <p class="comment-author">
                                                {{
                                                    comment.user?.name ||
                                                    "অতিথি"
                                                }}
                                            </p>
                                            <p class="comment-text">
                                                {{ comment.body }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== RELATED NEWS ==================== -->
                            <div
                                v-if="relatedNews?.length"
                                class="related-section"
                            >
                                <h2 class="related-title">
                                    <i class="fa-solid fa-newspaper"></i>
                                    এ বিভাগের আরো সংবাদ
                                </h2>

                                <div class="related-grid">
                                    <Link
                                        v-for="related in relatedNews.slice(
                                            0,
                                            6,
                                        )"
                                        :key="related.id"
                                        :href="
                                            route('news.details', related.slug)
                                        "
                                        class="related-card"
                                    >
                                        <div class="related-img-wrap">
                                            <img
                                                class="related-img"
                                                :src="
                                                    related.image
                                                        ? `/storage/${related.image}`
                                                        : '/images/default.jpg'
                                                "
                                                :alt="related.title"
                                                loading="lazy"
                                                @error="
                                                    (e) =>
                                                        (e.target.src =
                                                            '/images/default.jpg')
                                                "
                                            />
                                            <span
                                                class="related-overlay"
                                            ></span>
                                        </div>
                                        <p class="related-news-title">
                                            {{ truncate(related.title, 10) }}
                                        </p>
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </main>

                    <!-- ==================================================
                         SIDEBAR (1/3)
                    =================================================== -->
                    <aside class="lg:col-span-1">
                        <div class="sidebar-stack">
                            <!-- ==================== NEWS TABS ==================== -->
                            <div class="sidebar-card">
                                <div class="sidebar-tabs" role="tablist">
                                    <button
                                        class="sidebar-tab"
                                        :class="{
                                            active: activeFilter === 'latest',
                                        }"
                                        role="tab"
                                        :aria-selected="
                                            activeFilter === 'latest'
                                        "
                                        @click="activeFilter = 'latest'"
                                    >
                                        <i class="fa-regular fa-clock"></i>
                                        সর্বশেষ
                                    </button>
                                    <button
                                        class="sidebar-tab"
                                        :class="{
                                            active: activeFilter === 'popular',
                                        }"
                                        role="tab"
                                        :aria-selected="
                                            activeFilter === 'popular'
                                        "
                                        @click="activeFilter = 'popular'"
                                    >
                                        <i class="fa-solid fa-fire"></i>
                                        জনপ্রিয়
                                    </button>
                                </div>

                                <div class="sidebar-list">
                                    <Link
                                        v-for="(item, i) in filteredNews.slice(
                                            0,
                                            10,
                                        )"
                                        :key="item.id"
                                        :href="route('news.details', item.slug)"
                                        class="sidebar-item"
                                    >
                                        <span
                                            class="sidebar-rank"
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
                                        <div class="sidebar-thumb-wrap">
                                            <img
                                                class="sidebar-thumb"
                                                :src="
                                                    item.image
                                                        ? `/storage/${item.image}`
                                                        : '/images/default.jpg'
                                                "
                                                :alt="item.title"
                                                loading="lazy"
                                                @error="
                                                    (e) =>
                                                        (e.target.src =
                                                            '/images/default.jpg')
                                                "
                                            />
                                        </div>
                                        <p class="sidebar-title">
                                            {{ truncate(item.title, 9) }}
                                        </p>
                                    </Link>

                                    <div
                                        v-if="filteredNews.length === 0"
                                        class="sidebar-empty"
                                    >
                                        <i class="fa-regular fa-newspaper"></i>
                                        <p>কোনো সংবাদ পাওয়া যায়নি</p>
                                    </div>
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

                                    <div
                                        v-if="loadingPrayer"
                                        class="prayer-loading"
                                    >
                                        <div
                                            v-for="n in 5"
                                            :key="n"
                                            class="prayer-skeleton"
                                        ></div>
                                    </div>

                                    <div v-else class="prayer-table">
                                        <div
                                            v-for="prayer in prayerTimes"
                                            :key="prayer.key"
                                            class="prayer-row"
                                            :class="{
                                                active:
                                                    prayer.key ===
                                                    nextPrayerKey,
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

                                    <div class="sun-info">
                                        <div class="sun-item">
                                            <i
                                                class="fa-solid fa-sun sun-rise"
                                            ></i>
                                            <span>সূর্যোদয়</span>
                                            <strong>{{ sunrise }}</strong>
                                        </div>
                                        <div class="sun-item">
                                            <i
                                                class="fa-solid fa-moon sun-set"
                                            ></i>
                                            <span>সূর্যাস্ত</span>
                                            <strong>{{ sunset }}</strong>
                                        </div>
                                    </div>

                                    <div class="countdown">
                                        <p class="countdown-label">
                                            পরবর্তী নামাজ
                                        </p>
                                        <p class="countdown-prayer">
                                            {{ nextPrayer }}
                                        </p>
                                        <p class="countdown-time">
                                            {{ countdown }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== PROMO IMAGES ==================== -->
                            <div class="promo-card">
                                <a href="#" class="promo-link">
                                    <div class="promo-header">
                                        গতকালের জনপ্রিয় খবর দেখুন এক ক্লিকে
                                    </div>
                                    <img
                                        class="promo-img"
                                        :src="'/storage/images/1.gif'"
                                        alt="promo"
                                        loading="lazy"
                                    />
                                </a>
                            </div>

                            <div class="promo-card">
                                <img
                                    class="promo-img-full"
                                    :src="'/storage/images/taka.png'"
                                    alt="promo"
                                    loading="lazy"
                                />
                            </div>

                            <!-- ==================== CALENDAR ==================== -->
                            <div class="calendar-card">
                                <h3 class="calendar-title">
                                    <i class="fa-regular fa-calendar"></i>
                                    আর্কাইভ ক্যালেন্ডার
                                </h3>

                                <div class="calendar-selects">
                                    <select
                                        v-model="selectedMonth"
                                        class="cal-select"
                                        aria-label="Month"
                                    >
                                        <option
                                            v-for="m in months"
                                            :key="m.en"
                                            :value="m"
                                        >
                                            {{ m.bn }}
                                        </option>
                                    </select>
                                    <select
                                        v-model="selectedYear"
                                        class="cal-select year"
                                        aria-label="Year"
                                    >
                                        <option
                                            v-for="y in years"
                                            :key="y"
                                            :value="y"
                                        >
                                            {{ y }}
                                        </option>
                                    </select>
                                </div>

                                <div class="calendar-weekdays">
                                    <span
                                        v-for="wd in weekDays"
                                        :key="wd"
                                        class="weekday"
                                    >
                                        {{ wd }}
                                    </span>
                                </div>

                                <div class="calendar-days">
                                    <div
                                        v-for="(day, index) in calendarDays"
                                        :key="index"
                                        class="cal-day"
                                        :class="{
                                            today: isToday(day),
                                            future: day && isFuture(day),
                                            active: day && !isFuture(day),
                                            empty: !day,
                                        }"
                                        @click="
                                            day &&
                                            !isFuture(day) &&
                                            goArchive(day)
                                        "
                                    >
                                        {{ day || "" }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            <!-- Hidden print area -->
            <div id="printArea" class="hidden">
                <h1>{{ news.title }}</h1>
                <img
                    v-if="news.image"
                    :src="`/storage/${news.image}`"
                    :alt="news.title"
                />
                <div v-html="news.description"></div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
aside {
    background-color: transparent;
}
/* ==================== PAGE ==================== */
.article-page {
    background: #f1f5f9;
    min-height: 100vh;
    font-family: "Hind Siliguri", "Roboto", sans-serif;
}

/* ==================== ARTICLE CARD ==================== */
.article-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 18px;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
}

@media (min-width: 640px) {
    .article-card {
        padding: 24px;
    }
}

@media (min-width: 1024px) {
    .article-card {
        padding: 28px;
    }
}

/* ==================== BREADCRUMB ==================== */
.article-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 0.82rem;
}

.crumb-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: #f1f5f9;
    color: #475569;
    font-weight: 500;
    border-radius: 8px;
    text-decoration: none;
    transition:
        background 0.2s,
        color 0.2s;
}

.crumb-link:hover {
    background: #e2e8f0;
    color: #00930c;
}

.crumb-sep {
    color: #94a3b8;
    font-size: 0.65rem;
}

/* ==================== TITLE ==================== */
.article-title {
    font-size: 1.3rem;
    font-weight: 800;
    line-height: 1.35;
    color: #0f172a;
    margin: 0 0 16px;
    letter-spacing: -0.2px;
}

@media (min-width: 640px) {
    .article-title {
        font-size: 1.6rem;
    }
}

@media (min-width: 1024px) {
    .article-title {
        font-size: 1.85rem;
    }
}

/* ==================== META ==================== */
.article-meta {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 14px 0;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 18px;
}

@media (min-width: 640px) {
    .article-meta {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.author-block {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.author-img {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e2e8f0;
    flex-shrink: 0;
}

@media (min-width: 640px) {
    .author-img {
        width: 54px;
        height: 54px;
    }
}

.author-info {
    min-width: 0;
}

.author-name {
    margin: 0;
    color: #0f172a;
    font-size: 0.88rem;
    font-weight: 700;
    line-height: 1.4;
}

.author-types {
    color: #00930c;
    font-weight: 600;
    font-size: 0.78rem;
}

.author-location {
    margin: 3px 0 0;
    color: #64748b;
    font-size: 0.76rem;
    display: flex;
    align-items: center;
    gap: 4px;
}

.author-location i {
    color: #ef4444;
    font-size: 0.7rem;
}

.meta-right {
    flex-shrink: 0;
}

.meta-date {
    margin: 0;
    color: #64748b;
    font-size: 0.78rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ==================== HERO IMAGE ==================== */
.article-hero {
    width: 100%;
    aspect-ratio: 16 / 9;
    border-radius: 10px;
    overflow: hidden;
    background: #e2e8f0;
    margin-bottom: 18px;
}

.article-hero-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* ==================== ACTIONS ==================== */
.article-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 10px 6px;
    background: #ffffff;
    color: #334155;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}

.action-btn i {
    font-size: 0.95rem;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.action-btn.print:hover {
    background: #00930c;
    color: #fff;
    border-color: #00930c;
}

.action-btn.copy:hover {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

.action-btn.listen:hover {
    background: #10b981;
    color: #fff;
    border-color: #10b981;
}

.action-btn.stop {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
}

.action-btn.stop:hover {
    background: #dc2626;
}

/* ==================== CONTENT ==================== */
.article-content {
    color: #1e293b;
    font-size: 0.98rem;
    line-height: 1.85;
    text-align: justify;
    margin-bottom: 20px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.article-content :deep(p) {
    margin: 0 0 1em;
}

.article-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1em 0;
    display: block;
}

.article-content :deep(h2),
.article-content :deep(h3) {
    color: #0f172a;
    font-weight: 700;
    margin: 1.4em 0 0.6em;
    line-height: 1.3;
}

.article-content :deep(h2) {
    font-size: 1.3rem;
}

.article-content :deep(h3) {
    font-size: 1.15rem;
}

.article-content :deep(a) {
    color: #00930c;
    text-decoration: underline;
}

.article-content :deep(blockquote) {
    border-left: 4px solid #00930c;
    padding-left: 16px;
    margin: 1em 0;
    color: #475569;
    font-style: italic;
    background: #f0fdf4;
    padding: 12px 16px;
    border-radius: 6px;
}

.article-content :deep(ul),
.article-content :deep(ol) {
    padding-left: 24px;
    margin: 1em 0;
}

.article-content :deep(li) {
    margin: 4px 0;
}

.article-content :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1em 0;
}

.article-content :deep(td),
.article-content :deep(th) {
    border: 1px solid #e2e8f0;
    padding: 8px;
}

@media (min-width: 640px) {
    .article-content {
        font-size: 1.02rem;
    }
}

/* ==================== APPROVAL ==================== */
.approval-bar {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #f0fdf4;
    color: #065f46;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 0.82rem;
    margin-bottom: 16px;
}

.approval-bar i {
    color: #00930c;
    font-size: 0.95rem;
}

.approval-bar strong {
    font-weight: 700;
}

/* ==================== TAGS ==================== */
.article-tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.tags-label {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 600;
}

.tag-item {
    display: inline-block;
    padding: 5px 12px;
    background: #f1f5f9;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 500;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s;
}

.tag-item:hover {
    background: #00930c;
    color: #fff;
    border-color: #00930c;
}

/* ==================== SHARE ==================== */
.share-section {
    padding: 16px;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.share-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
    text-align: center;
}

.share-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

@media (min-width: 480px) {
    .share-buttons {
        grid-template-columns: repeat(4, 1fr);
    }
}

.share-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 12px;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 8px;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
}

.share-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.share-btn.facebook {
    background: #1877f2;
}

.share-btn.twitter {
    background: #0f1419;
}

.share-btn.linkedin {
    background: #0a66c2;
}

.share-btn.whatsapp {
    background: #25d366;
}

.share-btn i {
    font-size: 0.9rem;
}

/* ==================== COMMENTS ==================== */
.comments-section {
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    margin-bottom: 24px;
}

.comments-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 14px;
}

.comments-title i {
    color: #00930c;
    font-size: 0.95rem;
}

.comment-form {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

@media (min-width: 480px) {
    .comment-form {
        flex-direction: row;
    }
}

.comment-input {
    flex: 1;
    padding: 11px 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.88rem;
    font-family: inherit;
    transition:
        border-color 0.2s,
        box-shadow 0.2s,
        background 0.2s;
    outline: none;
}

.comment-input:focus {
    border-color: #00930c;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(0, 147, 12, 0.12);
}

.comment-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 11px 20px;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.25);
    font-family: inherit;
}

.comment-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(0, 147, 12, 0.35);
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.comment-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 24px 12px;
    color: #94a3b8;
    text-align: center;
}

.comment-empty i {
    font-size: 26px;
    opacity: 0.5;
}

.comment-empty p {
    margin: 0;
    font-size: 0.85rem;
}

.comment-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.comment-avatar {
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}

.comment-body {
    min-width: 0;
    flex: 1;
}

.comment-author {
    margin: 0 0 4px;
    color: #0f172a;
    font-size: 0.82rem;
    font-weight: 700;
}

.comment-text {
    margin: 0;
    color: #334155;
    font-size: 0.85rem;
    line-height: 1.55;
    word-break: break-word;
}

/* ==================== RELATED ==================== */
.related-section {
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.related-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 14px;
}

.related-title i {
    color: #00930c;
    font-size: 0.95rem;
}

.related-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

@media (min-width: 480px) {
    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 768px) {
    .related-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.related-card {
    display: block;
    text-decoration: none;
    border-radius: 10px;
    overflow: hidden;
    background: #f8fafc;
    transition:
        transform 0.25s,
        box-shadow 0.25s;
    border: 1px solid #e2e8f0;
}

.related-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.related-img-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #e2e8f0;
    isolation: isolate;
}

.related-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s;
}

.related-card:hover .related-img {
    transform: scale(1.08);
}

.related-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        transparent 55%,
        rgba(0, 0, 0, 0.4) 100%
    );
    pointer-events: none;
}

.related-news-title {
    padding: 10px 12px 12px;
    margin: 0;
    color: #1e293b;
    font-size: 0.83rem;
    line-height: 1.45;
    font-weight: 600;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-card:hover .related-news-title {
    color: #00930c;
}

/* ==================== SIDEBAR ==================== */
.sidebar-stack {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
}

.sidebar-tabs {
    display: flex;
    gap: 6px;
    padding: 8px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.sidebar-tab {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 12px;
    background: transparent;
    color: #64748b;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.2s,
        color 0.2s,
        box-shadow 0.2s;
    font-family: inherit;
}

.sidebar-tab:hover {
    color: #0f172a;
    background: #eef2f6;
}

.sidebar-tab.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.28);
}

.sidebar-list {
    padding: 8px;
    max-height: 500px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.sidebar-list::-webkit-scrollbar {
    width: 6px;
}

.sidebar-list::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.sidebar-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 8px;
    text-decoration: none;
    border-radius: 8px;
    border-bottom: 1px dashed #e2e8f0;
    transition: background 0.15s;
}

.sidebar-item:last-child {
    border-bottom: none;
}

.sidebar-item:hover {
    background: #f0fdf4;
}

.sidebar-item:hover .sidebar-title {
    color: #00930c;
}

.sidebar-rank {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.sidebar-rank.gold {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: #fff;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
}

.sidebar-rank.silver {
    background: linear-gradient(135deg, #cbd5e1, #94a3b8);
    color: #fff;
}

.sidebar-rank.bronze {
    background: linear-gradient(135deg, #d97706, #b45309);
    color: #fff;
}

.sidebar-thumb-wrap {
    flex-shrink: 0;
    width: 74px;
    height: 54px;
    border-radius: 6px;
    overflow: hidden;
    background: #e2e8f0;
}

.sidebar-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s;
}

.sidebar-item:hover .sidebar-thumb {
    transform: scale(1.08);
}

.sidebar-title {
    flex: 1;
    margin: 0;
    color: #1e293b;
    font-size: 0.84rem;
    font-weight: 500;
    line-height: 1.4;
    transition: color 0.2s;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.sidebar-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
    color: #94a3b8;
    gap: 8px;
    text-align: center;
}

.sidebar-empty i {
    font-size: 28px;
    opacity: 0.5;
}

.sidebar-empty p {
    margin: 0;
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
}

.prayer-heading i {
    color: #00930c;
    font-size: 0.95rem;
}

.prayer-date {
    text-align: center;
    color: #64748b;
    font-size: 0.78rem;
    margin: 0 0 12px;
}

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
}

.prayer-row.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.25);
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

/* ==================== PROMO ==================== */
.promo-card {
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 4px 12px rgba(0, 0, 0, 0.04);
}

.promo-link {
    display: block;
    text-decoration: none;
}

.promo-header {
    padding: 10px 14px;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    text-align: center;
}

.promo-img {
    width: 100%;
    display: block;
}

.promo-img-full {
    width: 100%;
    display: block;
    border-radius: 12px;
}

/* ==================== CALENDAR ==================== */
.calendar-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 14px;
    box-shadow:
        0 1px 3px rgba(0, 0, 0, 0.05),
        0 8px 24px rgba(0, 0, 0, 0.06);
}

.calendar-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
}

.calendar-title i {
    color: #00930c;
    font-size: 0.85rem;
}

.calendar-selects {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.cal-select {
    flex: 1;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.83rem;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    outline: none;
    font-family: inherit;
    transition: border-color 0.2s;
}

.cal-select:hover,
.cal-select:focus {
    border-color: #00930c;
}

.cal-select.year {
    flex: 0 0 90px;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    margin-bottom: 4px;
}

.weekday {
    text-align: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    padding: 6px 0;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.cal-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 6px;
    color: #334155;
    background: #f8fafc;
    cursor: default;
    transition:
        background 0.15s,
        color 0.15s,
        transform 0.15s;
}

.cal-day.active {
    cursor: pointer;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.cal-day.active:hover {
    background: #f0fdf4;
    color: #00930c;
    border-color: #bbf7d0;
    transform: scale(1.05);
}

.cal-day.today {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    border-color: #00930c;
    box-shadow: 0 2px 8px rgba(0, 147, 12, 0.3);
}

.cal-day.future {
    color: #cbd5e1;
    background: #f8fafc;
    cursor: not-allowed;
    opacity: 0.6;
}

.cal-day.empty {
    background: transparent;
    cursor: default;
}

/* ==================== SMALL PHONES ==================== */
@media (max-width: 360px) {
    .article-title {
        font-size: 1.15rem;
    }
    .article-actions {
        gap: 4px;
        padding: 6px;
    }
    .action-btn {
        font-size: 0.66rem;
        padding: 8px 4px;
    }
    .action-btn i {
        font-size: 0.85rem;
    }
    .sidebar-title {
        font-size: 0.78rem;
    }
    .sidebar-thumb-wrap {
        width: 62px;
        height: 48px;
    }
    .share-buttons {
        grid-template-columns: 1fr 1fr;
    }
    .prayer-row {
        font-size: 0.76rem;
        padding: 7px 8px;
    }
    .countdown-time {
        font-size: 1.2rem;
    }
    .cal-day {
        font-size: 0.72rem;
    }
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .article-title {
        font-size: 2rem;
    }
    .article-content {
        font-size: 1.05rem;
    }
    .sidebar-thumb-wrap {
        width: 92px;
        height: 66px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .action-btn,
    .share-btn,
    .comment-submit,
    .related-card,
    .related-img,
    .sidebar-thumb,
    .prayer-skeleton,
    .cal-day {
        transition: none;
        animation: none;
    }
    .action-btn:hover,
    .share-btn:hover,
    .comment-submit:hover,
    .related-card:hover,
    .cal-day.active:hover {
        transform: none;
    }
    .related-card:hover .related-img,
    .sidebar-item:hover .sidebar-thumb {
        transform: none;
    }
}
</style>
