<script setup>
import Layout from "@/Components/Layout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { computed, onMounted, onUnmounted, reactive, ref } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    news: Object,
    relatedNews: Array,
});

const page = usePage();
const countdown = ref("");
const prayerTimes = ref([]);
const dateText = ref("");
const sunrise = ref("");
const sunset = ref("");
const nextPrayer = ref("");
const nextPrayerKey = ref("");
let countdownInterval = null;

const form = reactive({ body: "" });

onMounted(() => {
    setTimeout(() => {
        axios
            .post(route("news.count.view"), {
                news_id: props.news.id,
            })
            .then((response) => {
                props.news.current_view = response.data.current_view;

                props.news.total_view = response.data.total_view;
            })
            .catch((error) => {
                console.log(error);
            });
    }, 5000); // Count after 5 seconds
});

function submitComment(newsId) {
    router.post(`/news/${newsId}/comments`, form, {
        onSuccess: () => {
            form.body = "";
        },
    });
}

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

const prayerNameMap = {
    Fajr: "ফজর",
    Sunrise: "সূর্যোদয়",
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
        sunrise.value = timings.Sunrise || "নির্ধারিত নেই";
        sunset.value = timings.Sunset || "নির্ধারিত নেই";

        const nextKey = getNextPrayer(timings);
        nextPrayerKey.value = nextKey;

        const nextTime = timings[nextKey] ?? "নির্ধারিত নেই";
        nextPrayer.value = `${prayerNameMap[nextKey] || nextKey} - ${nextTime}`;

        startCountdown(nextKey, timings);

        prayerTimes.value = [
            {
                key: "Fajr",
                name: "ফজর",
                part: "ভোর",
                time: timings.Fajr || "নির্ধারিত নেই",
            },
            {
                key: "Dhuhr",
                name: "জোহর",
                part: "বেলা",
                time: timings.Dhuhr || "নির্ধারিত নেই",
            },
            {
                key: "Asr",
                name: "আসর",
                part: "বিকেল",
                time: timings.Asr || "নির্ধারিত নেই",
            },
            {
                key: "Maghrib",
                name: "মাগরিব",
                part: "সন্ধ্যা",
                time: timings.Maghrib || "নির্ধারিত নেই",
            },
            {
                key: "Isha",
                name: "ইশা",
                part: "রাত",
                time: timings.Isha || "নির্ধারিত নেই",
            },
        ];
    } catch (error) {
        console.error("Failed to fetch prayer times:", error);
    }
};

onMounted(fetchPrayerTimes);

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
});

const months = [
    { en: "January", bn: "জানুয়ারি", index: 0 },
    { en: "February", bn: "ফেব্রুয়ারি", index: 1 },
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

const weekDays = ["রবি", "সোম", "মঙ্গল", "বুধ", "বৃহস্পতি", "শুক্র", "শনি"];
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

const isToday = (day) => {
    return (
        day &&
        day === todayDate &&
        selectedMonth.value.index === todayMonth &&
        selectedYear.value === todayYear
    );
};

const isFuture = (day) => {
    if (!day) return false;
    const date = new Date(selectedYear.value, selectedMonth.value.index, day);
    return date > new Date();
};

const goArchive = (day) => {
    const date = new Date(selectedYear.value, selectedMonth.value.index, day);

    const formattedDate = `${date.getFullYear()}-${(date.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;

    router.get(route("news.archive"), {
        date: formattedDate,
    });
};

const latestNews = page.props.sidebarLatest ?? [];
const popularNews = page.props.sidebarPopular ?? [];
const activeFilter = ref("latest");
const filteredNews = computed(() => {
    return activeFilter.value === "latest" ? latestNews : popularNews;
});
const setFilter = (filter) => {
    activeFilter.value = filter;
};
const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        alert("নিউজ লিঙ্ক কপি হয়েছে");
    } catch (err) {
        alert("কপি করতে সমস্যা হয়েছে");
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
                body { font-family: sans-serif; padding: 20px; }
                img { max-width: 100%; height: auto; margin-bottom: 20px; }
                h1 { font-size: 24px; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            ${content}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
};

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

    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);
};

const stopReading = () => {
    window.speechSynthesis.cancel();
};

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
        " ": " ",
    };

    return text
        .split("")
        .map((char) => map[char] || char)
        .join("");
}

const tags = computed(() => {
    try {
        return typeof props.news.tag === "string"
            ? JSON.parse(props.news.tag)
            : props.news.tag || [];
    } catch {
        return [];
    }
});
const newsUrl = computed(() => window.location.href);
const newsTitle = computed(() => props.news.title);

const shareLinks = computed(() => ({
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(newsUrl.value)}`,
    twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(newsTitle.value)}&url=${encodeURIComponent(newsUrl.value)}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(newsUrl.value)}`,
    whatsapp: `https://api.whatsapp.com/send?text=${encodeURIComponent(newsTitle.value)}%20${encodeURIComponent(newsUrl.value)}`,
}));
</script>

<template>
    <Layout>
        <Head title="News Details Page" />
        <div class="container mx-auto px-4 my-5">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Main Content -->
                <div
                    class="w-full md:w-2/3 bg-slate-200 shadow p-2 h-fit rounded"
                >
                    <!-- Breadcrumb -->
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center gap-2 bg-slate-300 p-2 rounded"
                    >
                        <Link
                            :href="route('home')"
                            class="text-white bg-red-500 p-2 rounded"
                        >
                            <i class="fa-regular fa-house"></i> মূল পাতা
                        </Link>
                        <Link
                            :href="route('all.news', 'jateey')"
                            class="bg-gray-500 text-white p-2 rounded"
                        >
                            <i class="fa-solid fa-bars"></i> জাতীয়
                        </Link>
                    </div>

                    <!-- News Title -->
                    <h2 class="text-xl font-semibold mt-4">{{ news.title }}</h2>

                    <!-- Reporter Info -->
                    <div
                        class="flex flex-col sm:flex-row gap-3 mt-4 items-start sm:items-center"
                    >
                        <div class="flex-shrink-0 w-10 sm:w-15 h-10 sm:h-15">
                            <img
                                class="rounded-full w-full h-full object-cover"
                                :src="
                                    news.user?.reporter_profile?.photo
                                        ? `/storage/${news.user.reporter_profile.photo}`
                                        : news.user?.image
                                          ? `/storage/${news.user.image}`
                                          : '/storage/images/default.jpg'
                                "
                            />
                        </div>
                        <div class="flex-1">
                            <div
                                class="border-b border-slate-300 pb-2 mb-2 text-sm"
                            >
                                <span class="block mb-1 font-medium">
                                    {{ news.user?.name ?? "স্টাফ রিপোর্টার" }}
                                    <!-- {{
                                        news.user?.reporter_types ??
                                        "স্টাফ রিপোর্টার"
                                    }} -->
                                    <small>
                                        ({{
                                            news.user?.reporter_profile?.types
                                                ?.map((t) => t.name)
                                                .join(", ") ||
                                            "ডেস্ক রিপোর্টার"
                                        }})
                                    </small>
                                </span>
                                <span
                                    class="block text-xs mb-1"
                                    v-if="news.user?.reporter_profile"
                                >
                                    <i class="fa-solid fa-location-dot"></i>
                                    {{
                                        news.user?.reporter_profile
                                            ?.division_data?.name ?? "N/A"
                                    }},
                                    {{
                                        news.user?.reporter_profile
                                            ?.district_data?.name ?? "N/A"
                                    }},
                                    {{
                                        news.user?.reporter_profile
                                            ?.upazila_data?.name ?? "N/A"
                                    }},
                                    {{
                                        news.user?.reporter_profile?.union_data
                                            ?.name ?? "N/A"
                                    }}
                                </span>
                                <span v-else>{{ "" }}</span>
                                <span
                                    class="block text-xs mt-1 sm:mt-0 sm:ml-4"
                                >
                                    <i class="fa-solid fa-clock"></i>
                                    প্রকাশের সময়: {{ formattedDate }}
                                    <!-- |
                                    {{ news.total_view ?? 0 }} বার পড়া হয়েছে |
                                    আজ {{ news.current_view ?? 0 }} বার -->
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- News Image -->
                    <img
                        class="w-full h-auto max-h-[450px] object-cover rounded mt-4"
                        :src="
                            news.image
                                ? `/storage/${news.image}`
                                : '/storage/images/default.jpg'
                        "
                        alt="news image"
                    />

                    <!-- Print and Copy buttons -->
                    <div
                        class="flex flex-col sm:flex-row justify-between gap-2 py-5"
                    >
                        <button
                            type="button"
                            @click="printNews"
                            class="border border-slate-300 rounded text-xs text-white bg-green-500 p-2 w-full sm:w-auto"
                        >
                            <i class="fa-solid fa-print"></i> খবর প্রিন্ট করুন
                        </button>
                        <button
                            type="button"
                            @click="copyLink"
                            class="border border-slate-300 bg-green-500 rounded text-white text-xs p-2 w-full sm:w-auto"
                        >
                            <i class="fa-regular fa-copy"></i> এই নিউজের লিঙ্ক
                            কপি করুন
                        </button>
                    </div>

                    <!-- News Description -->
                    <div id="news-description" class="prose mt-4 leading-7 p-3">
                        <p v-html="news.description" class="mb-0"></p>
                    </div>

                    <!-- Approval -->
                    <div class="border-b border-b-slate-400 mb-3 mt-4">
                        <span class="text-sm font-bold block mb-3">
                            অনুমোদন করেছেন:
                            <small class="text-xs">মাসুদ - ডেক্স রিপোর্ট</small>
                        </span>
                    </div>

                    <!-- Listen / Stop Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 mb-4">
                        <button
                            @click="speakNews"
                            class="border border-slate-300 rounded p-2 bg-green-600 text-white flex-1"
                        >
                            <i class="fa-solid fa-volume-high"></i> খবর শুনুন
                        </button>
                        <button
                            @click="stopReading"
                            class="border border-slate-300 rounded p-2 bg-red-600 text-white flex-1"
                        >
                            <i class="fa-solid fa-stop"></i> বন্ধ করুন
                        </button>
                    </div>

                    <!-- Tags -->
                    <div class="flex flex-wrap gap-2 mb-5">
                        <button
                            v-for="tag in tags"
                            :key="tag"
                            class="p-2 rounded border border-slate-400 hover:bg-gray-400 text-sm"
                        >
                            {{ tag }}
                        </button>
                    </div>

                    <!-- Share Section -->
                    <div class="mb-5">
                        <h2 class="text-xl font-robo font-semibold mb-2">
                            সংবাদ টি ভালো লাগলে শেয়ার করুন
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            <a
                                :href="shareLinks.facebook"
                                target="_blank"
                                class="p-2 border rounded bg-green-600 hover:bg-transparent text-white hover:text-black"
                            >
                                <i class="fa-brands fa-facebook-f"></i> Facebook
                            </a>
                            <a
                                :href="shareLinks.twitter"
                                target="_blank"
                                class="p-2 border rounded bg-red-600 hover:bg-transparent text-white hover:text-black"
                            >
                                <i class="fa-brands fa-twitter"></i> Twitter
                            </a>
                            <a
                                :href="shareLinks.linkedin"
                                target="_blank"
                                class="p-2 border rounded bg-blue-600 hover:bg-transparent text-white hover:text-black"
                            >
                                <i class="fa-brands fa-linkedin-in"></i>
                                Linkedin
                            </a>
                            <a
                                :href="shareLinks.whatsapp"
                                target="_blank"
                                class="p-2 border rounded bg-green-400 hover:bg-transparent text-white hover:text-black"
                            >
                                <i class="fa-brands fa-whatsapp"></i> Whatsapp
                            </a>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="mb-5">
                        <h2 class="text-xl font-robo font-semibold mb-2">
                            কমেন্ট করুন
                        </h2>
                        <form
                            @submit.prevent="submitComment(news.id)"
                            class="flex flex-col sm:flex-row gap-2"
                        >
                            <input
                                v-model="form.body"
                                placeholder="কমেন্ট করুন...."
                                class="form-input flex-1"
                            />
                            <button
                                type="submit"
                                class="btn rounded w-full sm:w-auto"
                            >
                                Submit
                            </button>
                        </form>

                        <ul class="mt-4">
                            <li
                                v-for="comment in news.comments"
                                :key="comment.id"
                                class="pb-2"
                            >
                                {{ comment.body }}
                            </li>
                        </ul>
                    </div>

                    <!-- Related News -->
                    <div class="p-2 bg-green-500 mb-3">
                        <span class="text-lg font-semibold font-robo text-white"
                            >এ বিভাগের আরো সংবাদ</span
                        >
                    </div>
                    <div class="flex flex-wrap justify-center gap-3 mt-2">
                        <Link
                            v-for="related in relatedNews.slice(0, 9)"
                            :key="related.id"
                            :href="`/news/details/${related.slug}`"
                            class="w-full sm:w-[250px] relative group"
                        >
                            <img
                                class="w-full h-[150px] object-cover rounded"
                                :src="
                                    related.image
                                        ? `/storage/${related.image}`
                                        : '/storage/images/default.jpg'
                                "
                                :alt="related.title"
                            />
                            <div
                                class="absolute bottom-0 left-0 w-full p-2 bg-black/50 z-10 transition-all duration-200 group-hover:bg-black/70"
                            >
                                <p
                                    class="font-medium leading-6 text-white text-sm"
                                >
                                    {{
                                        related.title
                                            .split(" ")
                                            .slice(0, 10)
                                            .join(" ")
                                    }}{{
                                        related.title.split(" ").length > 10
                                            ? "..."
                                            : ""
                                    }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="w-full md:w-1/3 flex flex-col h-fit gap-4">
                    <!-- Latest / Popular -->
                    <div class="flex w-full mb-3">
                        <button
                            :class="[
                                'w-1/2 py-3 text-white',
                                activeFilter === 'latest'
                                    ? 'bg-black'
                                    : 'bg-slate-400',
                            ]"
                            @click="setFilter('latest')"
                        >
                            সর্বশেষ সংবাদ
                        </button>
                        <button
                            :class="[
                                'w-1/2 py-3 text-white',
                                activeFilter === 'popular'
                                    ? 'bg-black'
                                    : 'bg-slate-400',
                            ]"
                            @click="setFilter('popular')"
                        >
                            জনপ্রিয় সংবাদ
                        </button>
                    </div>

                    <!-- Filtered News -->
                    <div class="p-3 bg-slate-300">
                        <div
                            class="bg-white p-2 h-[420px] sm:h-[500px] overflow-y-auto"
                        >
                            <div
                                v-for="item in filteredNews.slice(0, 10)"
                                :key="item.id"
                                class="flex gap-3 border-b border-dotted pb-1 mt-1"
                            >
                                <div class="w-[80px] sm:w-[100px]">
                                    <img
                                        :src="
                                            item.image
                                                ? `/storage/${item.image}`
                                                : '/storage/images/default.jpg'
                                        "
                                        class="w-full h-full object-cover rounded"
                                        alt="news"
                                    />
                                </div>
                                <div>
                                    <Link :href="`/news/details/${item.slug}`">
                                        <p
                                            class="leading-6 hover:text-blue-500 font-medium text-sm sm:text-base"
                                        >
                                            {{
                                                item.title
                                                    .split(" ")
                                                    .slice(0, 8)
                                                    .join(" ")
                                            }}{{
                                                item.title.split(" ").length >
                                                10
                                                    ? "..."
                                                    : ""
                                            }}
                                        </p>
                                    </Link>
                                </div>
                            </div>
                            <div
                                v-if="filteredNews.length === 0"
                                class="text-center text-gray-500 py-5"
                            >
                                কোনো সংবাদ পাওয়া যায়নি।
                            </div>
                        </div>
                    </div>

                    <!-- Prayer Times & Zodiac -->
                    <div class="overflow-x-auto">
                        <div
                            class="border rounded p-3 mt-4 bg-cover bg-center bg-no-repeat"
                            style="
                                background-image: url(&quot;/storage/images/bg-mosque.jpeg&quot;);
                            "
                        >
                            <h2
                                class="text-lg text-center text-green-700 font-bold prayh2"
                            >
                                আজকের নামাজের সময়সূচি
                            </h2>

                            <p class="text-center text-slate-500">
                                {{ dateText }}
                            </p>

                            <table
                                class="w-full border rounded border-green-700 mt-2"
                            >
                                <tbody>
                                    <tr
                                        v-for="(prayer, index) in prayerTimes"
                                        :key="index"
                                        :class="[
                                            'flex justify-between text-center',
                                            prayer.key === nextPrayerKey
                                                ? 'bg-green-100 font-bold text-green-800'
                                                : 'text-black',
                                        ]"
                                    >
                                        <td class="p-2 w-full">
                                            {{ prayer.name }}
                                        </td>
                                        <td class="p-2 w-full">
                                            {{ prayer.part }}
                                        </td>
                                        <td class="p-2 w-full">
                                            {{ prayer.time }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="mt-3 text-sm">
                                <div class="flex justify-between">
                                    <p class="text-red-500">
                                        আজ সূর্যাস্ত - {{ sunset }}
                                    </p>
                                    <p class="text-red-500">
                                        আজ সূর্যোদয় - {{ sunrise }}
                                    </p>
                                </div>

                                <p
                                    class="text-center text-green-700 font-semibold mt-2"
                                >
                                    পরবর্তী নামাজ: {{ nextPrayer }}
                                    {{ countdown }}
                                </p>
                            </div>
                        </div>
                        <div class="my-5">
                            <a href="">
                                <div class="p-2 bg-green-500">
                                    <span
                                        class="text-lg font-semibold font-robo text-white"
                                        >গতকালের জনপ্রিয় খবর দেখুন এক
                                        ক্লিকে</span
                                    >
                                </div>
                                <img
                                    class="w-full"
                                    src="../../../../../public/storage/images/1.gif"
                                    alt="gif click"
                                />
                            </a>
                        </div>
                        <div class="my-5">
                            <img
                                src="../../../../../public/storage/images/taka.png"
                                class="w-full"
                                alt=""
                            />
                        </div>

                        <div class="my-5">
                            <div class="flex gap-2 mb-3">
                                <select
                                    v-model="selectedMonth"
                                    class="border p-2 flex-1"
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
                                    class="border p-2 w-24"
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

                            <div
                                class="grid grid-cols-7 text-center border border-blue-600"
                            >
                                <div
                                    v-for="(day, index) in calendarDays"
                                    :key="index"
                                    class="p-2 border h-12 flex items-center justify-center cursor-pointer"
                                    :class="{
                                        'bg-blue-50': day && !isFuture(day),
                                        'bg-gray-200 text-gray-400 cursor-not-allowed':
                                            day && isFuture(day),
                                        'bg-green-600 text-white font-bold':
                                            isToday(day),
                                    }"
                                    @click="
                                        day && !isFuture(day) && goArchive(day)
                                    "
                                >
                                    <span v-if="day">{{ day }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden print area -->
        <div id="printArea" class="hidden">
            <h1 class="text-xl font-bold mb-2">{{ news.title }}</h1>
            <img
                v-if="news.image"
                :src="`/storage/${news.image}`"
                alt="news image"
                class="mb-2 w-full"
            />
            <div v-html="news.description" class="prose"></div>
        </div>
    </Layout>
</template>
