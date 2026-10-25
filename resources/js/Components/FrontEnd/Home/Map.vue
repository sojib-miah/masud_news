<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import axios from "axios";
import SelectInput from "@/Shared/SelectInput.vue";
import Bangladesh from "./Bangladesh.vue";

const props = defineProps({
    divisions: Array,
    sarades: Object,
    bivag: Object,
    allNews: Array,
});
const activeTab = ref("sarades");

const filteredNews = computed(() => {
    if (activeTab.value === "sarades") {
        return props.allNews
            .filter((n) => n.category_id === props.sarades?.id)
            .slice(0, 10);
    }

    // Subcategory tab: match by subcategory_id
    return props.allNews
        .filter((n) => n.subcategory_id === Number(activeTab.value))
        .slice(0, 10);
});

const countdown = ref("");
const prayerTimes = ref([]);
const dateText = ref("");
const sunrise = ref("");
const sunset = ref("");
const nextPrayer = ref("");
const nextPrayerKey = ref("");
let countdownInterval = null;

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
        if (divisionId) {
            const res = await axios.get(`/districts/${divisionId}`);
            districts.value = res.data;
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
            const res = await axios.get(`/upazilas/${districtId}`);
            upazilas.value = res.data;
        } else {
            upazilas.value = [];
        }
    },
);
</script>

<template>
    <div class="py-4 bg-slate-600 text-white font-robo">
        <div class="container mx-auto px-3">
            <!-- Main Layout -->
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- LEFT -->
                <div class="w-full lg:w-2/3">
                    <!-- Tabs -->
                    <div class="bg-white p-3 rounded-2xl mb-4">
                        <ul
                            class="flex flex-wrap gap-2 sm:gap-4 justify-center items-center font-medium text-black"
                        >
                            <li>
                                <button
                                    @click="activeTab = 'sarades'"
                                    :class="
                                        activeTab === 'sarades' ? 'active' : ''
                                    "
                                >
                                    সারাদেশ
                                </button>
                            </li>

                            <li
                                v-for="sub in bivag?.subcategories || []"
                                :key="sub.id"
                            >
                                <button
                                    @click="activeTab = sub.id"
                                    :class="
                                        activeTab === sub.id ? 'active' : ''
                                    "
                                >
                                    {{ sub.name }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- News Grid -->
                    <div
                        class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-3"
                    >
                        <div
                            v-for="item in allNews.slice(0, 6)"
                            :key="item.id"
                            class="bg-white text-black p-2 sm:p-3 rounded shadow"
                        >
                            <img
                                class="mb-2 w-full h-[100px] sm:h-[120px] md:h-[130px] object-cover rounded"
                                :src="
                                    item.image
                                        ? `/storage/${item.image}`
                                        : '/storage/images/default.jpg'
                                "
                            />

                            <Link :href="route('news.show', item.slug)">
                                <p
                                    class="text-xs sm:text-sm text-blue-500 leading-5"
                                >
                                    {{
                                        item.title
                                            .split(" ")
                                            .slice(0, 10)
                                            .join(" ")
                                    }}
                                    {{
                                        item.title.split(" ").length > 10
                                            ? "..."
                                            : ""
                                    }}
                                </p>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="w-full lg:w-1/3">
                    <!-- Title -->
                    <div
                        class="bg-white p-3 rounded-2xl mb-4 text-center text-black"
                    >
                        এক ক্লিকে বিভাগের খবর
                    </div>

                    <!-- Map -->
                    <!-- <Bangladesh /> -->

                    <!-- Filter Form -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                        <SelectInput
                            class="text-black border rounded"
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
                            class="text-black border rounded"
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
                            class="text-black border rounded"
                            v-model="form.upazila"
                            :options="
                                upazilas.map((u) => ({
                                    label: u.bn_name,
                                    value: u.id,
                                }))
                            "
                            placeholder="উপজেলা"
                        />

                        <button
                            class="bg-green-500 p-2 sm:p-3 rounded w-full text-white"
                        >
                            অনুসন্ধান করুন
                        </button>
                    </div>

                    <!-- Prayer Time -->
                    <div
                        class="border rounded p-3 mt-4 bg-cover bg-center"
                        style="
                            background-image: url(&quot;/storage/images/bg-mosque.jpeg&quot;);
                        "
                    >
                        <h2
                            class="text-base sm:text-lg text-center text-green-700 font-bold prayh2"
                        >
                            আজকের নামাজের সময়সূচি
                        </h2>

                        <p class="text-center text-slate-500 text-sm">
                            {{ dateText }}
                        </p>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table
                                class="w-full border border-green-700 mt-2 text-sm"
                            >
                                <tbody>
                                    <tr
                                        v-for="(prayer, index) in prayerTimes"
                                        :key="index"
                                        :class="[
                                            'flex justify-between',
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
                        </div>

                        <!-- Extra Info -->
                        <div class="mt-3 text-xs sm:text-sm">
                            <div class="flex justify-between flex-wrap gap-2">
                                <p class="text-red-500">
                                    সূর্যাস্ত - {{ sunset }}
                                </p>
                                <p class="text-red-500">
                                    সূর্যোদয় - {{ sunrise }}
                                </p>
                            </div>

                            <p
                                class="text-center text-green-700 font-semibold mt-2"
                            >
                                {{ nextPrayer }} {{ countdown }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.active {
    color: white;
    background-image: linear-gradient(to right, #112e64, #3e6bb9);
    padding: 6px 10px;
    border-radius: 50px;
    font-size: 12px;
}

@media (min-width: 640px) {
    .active {
        font-size: 14px;
        padding: 8px 12px;
    }
}

.prayh2 {
    margin-bottom: 5px !important;
}

table tr td {
    border: 1px solid green !important;
}

ul,
li {
    list-style: none;
}
</style>
