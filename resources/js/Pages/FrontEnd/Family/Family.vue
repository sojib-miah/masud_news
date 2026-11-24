<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { ref, computed, watch } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    families: { type: Array, default: () => [] },
    reporters: { type: Array, default: () => [] },
    reporterTypes: { type: Array, default: () => [] },
    topReportersByNews: { type: Array, default: () => [] },
    topReportersByViews: { type: Array, default: () => [] },
});

const activeType = ref("all"); // "all" | numeric type id
const search = ref("");

const searchQuery = computed(() => search.value.trim().toLowerCase());

const filteredFamilies = computed(() => {
    const q = searchQuery.value;
    if (!q) return props.families;
    return props.families.filter(
        (f) =>
            f.name?.toLowerCase().includes(q) ||
            f.phone?.toLowerCase().includes(q),
    );
});

const filteredReporters = computed(() => {
    const q = searchQuery.value;
    return props.reporters.filter((r) => {
        // type filter
        if (activeType.value !== "all") {
            const id = Number(activeType.value);
            if (!r.reporter_types?.includes(id)) return false;
        }
        if (!q) return true;

        // search in name, phone, reporter_area, and type_names
        return (
            r.name?.toLowerCase().includes(q) ||
            r.phone?.toLowerCase().includes(q) ||
            r.reporter_area?.toLowerCase().includes(q) ||
            (r.type_names || []).some((n) => n.toLowerCase().includes(q))
        );
    });
});

function setType(id) {
    activeType.value = id;
}
function clearSearch() {
    search.value = "";
}

// pagination
const currentPage = ref(1);
const perPage = 50;

const totalPages = computed(() =>
    Math.ceil(filteredReporters.value.length / perPage),
);

const paginatedReporters = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredReporters.value.slice(start, start + perPage);
});

function changePage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

watch([search, activeType], () => {
    currentPage.value = 1;
});
</script>

<template>
    <Layout>
        <Head title="Our Family" />

        <div class="py-8">
            <div class="container mx-auto px-3 sm:px-4 lg:px-6">
                <!-- Hero -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl px-6 py-8 mb-6 text-white shadow-lg"
                >
                    <h1 class="text-2xl sm:text-3xl font-bold">
                        <i class="fa-solid fa-users mr-2"></i>আমাদের পরিবার
                    </h1>
                    <p class="text-green-100 text-sm mt-1">
                        আমাদের পরিবারের সদস্য এবং রিপোর্টারদের তালিকা
                    </p>
                </div>

                <!-- Filter + Search -->
                <div
                    class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-6"
                >
                    <div class="flex flex-col gap-3">
                        <div class="relative">
                            <i
                                class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"
                            ></i>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="নাম বা ফোন দিয়ে খুঁজুন..."
                                class="w-full border border-slate-300 rounded-lg pl-10 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                            />
                            <button
                                v-if="search"
                                type="button"
                                @click="clearSearch"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500"
                            >
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="setType('all')"
                                class="filter-chip"
                                :class="{ active: activeType === 'all' }"
                            >
                                <i class="fa-solid fa-users mr-1"></i>
                                সব রিপোর্টার
                            </button>
                            <button
                                v-for="t in props.reporterTypes"
                                :key="t.id"
                                type="button"
                                @click="setType(t.id)"
                                class="filter-chip"
                                :class="{ active: activeType === t.id }"
                            >
                                <i class="fa-solid fa-tag mr-1"></i>
                                {{ t.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Family section -->
                <section class="mb-10">
                    <h2
                        class="text-lg sm:text-xl font-bold text-slate-800 mb-4"
                    >
                        <i
                            class="fa-solid fa-house-chimney text-green-600 mr-2"
                        ></i>
                        পরিচালনা ও সম্পাদকীয় পরিষদ
                        <span class="text-sm font-normal text-slate-400">
                            ({{ filteredFamilies.length }})
                        </span>
                    </h2>

                    <div
                        v-if="filteredFamilies.length === 0"
                        class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-500"
                    >
                        <i
                            class="fa-regular fa-folder-open text-4xl text-slate-300 mb-2"
                        ></i>
                        <p class="text-sm">
                            কোনো ফ্যামিলি মেম্বার পাওয়া যায়নি
                        </p>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                    >
                        <Link
                            v-for="f in filteredFamilies"
                            :key="f.id"
                            :href="route('our.family.detail', f.id)"
                            class="family-card group"
                        >
                            <div
                                class="h-40 bg-green-50 overflow-hidden relative"
                            >
                                <img
                                    v-if="f.image_url"
                                    :src="f.image_url"
                                    :alt="f.name"
                                    class="w-full h-full object-contain group-hover:scale-105 transition duration-300"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center text-green-700 text-5xl font-bold"
                                >
                                    {{ (f.name || "?").charAt(0) }}
                                </div>
                                <span
                                    v-if="f.designation"
                                    class="absolute top-2 right-2 bg-green-600 text-white text-xs px-2 py-1 rounded-full shadow"
                                >
                                    {{ f.designation }}
                                </span>
                            </div>

                            <div class="p-4">
                                <h3
                                    class="font-bold text-slate-800 group-hover:text-green-700 transition"
                                >
                                    {{ f.name }}
                                </h3>
                                <p
                                    v-if="f.phone"
                                    class="text-sm text-slate-500 mt-1"
                                >
                                    <i
                                        class="fa-solid fa-phone text-green-600 mr-1"
                                    ></i>
                                    {{ f.phone }}
                                </p>
                                <p
                                    v-if="f.location"
                                    class="text-xs text-slate-400 mt-1"
                                >
                                    <i
                                        class="fa-solid fa-location-dot mr-1"
                                    ></i>
                                    {{ f.location }}
                                </p>
                                <p
                                    v-if="f.short_desc"
                                    class="text-xs text-slate-500 mt-2 line-clamp-2"
                                >
                                    {{ f.short_desc }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- ============ TOP 10 REPORTERS BY NEWS POSTS ============ -->
                <section class="mb-8">
                    <h2
                        class="text-lg sm:text-xl font-bold text-slate-800 mb-4"
                    >
                        <i class="fa-solid fa-id-badge text-green-600 mr-2"></i>
                        সর্বাধিক সংবাদ প্রকাশকারী রিপোর্টার
                        <span class="text-sm font-normal text-slate-400">
                            ({{ topReportersByNews.length }})
                        </span>
                    </h2>

                    <div
                        v-if="topReportersByNews.length"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4"
                    >
                        <Link
                            v-for="(r, i) in topReportersByNews"
                            :key="r.id"
                            :href="route('our.reporter.detail', r.id)"
                            class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition p-3 flex flex-col items-center text-center"
                        >
                            <span
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold mb-2"
                                :class="
                                    i === 0
                                        ? 'bg-amber-400 text-white'
                                        : i === 1
                                          ? 'bg-slate-400 text-white'
                                          : i === 2
                                            ? 'bg-orange-500 text-white'
                                            : 'bg-slate-100 text-slate-600'
                                "
                            >
                                {{ i + 1 }}
                            </span>

                            <img
                                v-if="r.photo_url"
                                :src="r.photo_url"
                                :alt="r.name"
                                loading="lazy"
                                class="w-16 h-16 rounded-full object-contain border-2 border-green-500 mb-2"
                            />
                            <div
                                v-else
                                class="w-16 h-16 rounded-full border-2 border-green-500 mb-2 flex items-center justify-center text-green-700 text-3xl font-bold"
                            >
                                {{ (r.name || "?").charAt(0) }}
                            </div>

                            <p
                                class="text-sm font-semibold text-slate-800 line-clamp-1"
                            >
                                {{ r.name }}
                            </p>

                            <p class="text-xs text-green-600 font-bold mt-1">
                                <i class="fa-solid fa-newspaper mr-1"></i>
                                {{ r.news_count }} নিউজ
                            </p>
                        </Link>
                    </div>

                    <p v-else class="text-sm text-slate-500">
                        কোনো রিপোর্টার পাওয়া যায়নি।
                    </p>
                </section>

                <!-- ============ TOP 10 REPORTERS BY TOTAL VIEWS ============ -->
                <section class="mb-8">
                    <h2
                        class="text-lg sm:text-xl font-bold text-slate-800 mb-4"
                    >
                        <i class="fa-solid fa-fire text-red-500 mr-2"></i>
                        সর্বাধিক ভিউ পাওয়া রিপোর্টার
                        <span class="text-sm font-normal text-slate-400">
                            ({{ topReportersByViews.length }})
                        </span>
                    </h2>

                    <div
                        v-if="topReportersByViews.length"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4"
                    >
                        <Link
                            v-for="(r, i) in topReportersByViews"
                            :key="r.id"
                            :href="route('our.reporter.detail', r.id)"
                            class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition p-3 flex flex-col items-center text-center"
                        >
                            <span
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold mb-2"
                                :class="
                                    i === 0
                                        ? 'bg-amber-400 text-white'
                                        : i === 1
                                          ? 'bg-slate-400 text-white'
                                          : i === 2
                                            ? 'bg-orange-500 text-white'
                                            : 'bg-slate-100 text-slate-600'
                                "
                            >
                                {{ i + 1 }}
                            </span>

                            <img
                                v-if="r.photo_url"
                                :src="r.photo_url"
                                :alt="r.name"
                                loading="lazy"
                                class="w-16 h-16 rounded-full object-contain border-2 border-red-500 mb-2"
                            />
                            <div
                                v-else
                                class="w-16 h-16 rounded-full border-2 border-green-500 mb-2 flex items-center justify-center text-green-700 text-3xl font-bold"
                            >
                                {{ (r.name || "?").charAt(0) }}
                            </div>

                            <p
                                class="text-sm font-semibold text-slate-800 line-clamp-1"
                            >
                                {{ r.name }}
                            </p>

                            <p class="text-xs text-red-600 font-bold mt-1">
                                <i class="fa-solid fa-eye mr-1"></i>
                                {{ r.total_views.toLocaleString() }} ভিউ
                            </p>
                        </Link>
                    </div>

                    <p v-else class="text-sm text-slate-500">
                        কোনো রিপোর্টার পাওয়া যায়নি।
                    </p>
                </section>

                <!-- Reporter section -->
                <section>
                    <h2
                        class="text-lg sm:text-xl font-bold text-slate-800 mb-4"
                    >
                        <i class="fa-solid fa-id-badge text-green-600 mr-2"></i>
                        রিপোর্টার
                        <span class="text-sm font-normal text-slate-400">
                            ({{ filteredReporters.length }})
                        </span>
                    </h2>

                    <div
                        v-if="filteredReporters.length === 0"
                        class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-500"
                    >
                        <i
                            class="fa-regular fa-folder-open text-4xl text-slate-300 mb-2"
                        ></i>
                        <p class="text-sm">কোনো রিপোর্টার পাওয়া যায়নি</p>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
                    >
                        <Link
                            v-for="r in paginatedReporters"
                            :key="r.id"
                            :href="route('our.reporter.detail', r.id)"
                            class="reporter-card group"
                        >
                            <div
                                class="h-40 bg-green-50 overflow-hidden relative"
                            >
                                <!-- Image wrapper: brightness + grayscale when not approved -->
                                <div
                                    class="w-full h-full transition duration-300"
                                    :class="
                                        r.is_approved
                                            ? ''
                                            : 'grayscale brightness-50'
                                    "
                                >
                                    <img
                                        v-if="r.photo_url"
                                        :src="r.photo_url"
                                        :alt="r.name"
                                        class="w-full h-full object-contain"
                                        :class="
                                            r.is_approved
                                                ? 'group-hover:scale-105 transition duration-300'
                                                : ''
                                        "
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-green-700 text-5xl font-bold"
                                    >
                                        {{ (r.name || "?").charAt(0) }}
                                    </div>
                                </div>

                                <!-- Optional: "Not approved" label overlay -->
                                <span
                                    v-if="!r.is_approved"
                                    class="absolute inset-0 flex items-center justify-center bg-black/40 text-white text-xs font-bold uppercase tracking-wide"
                                >
                                    <i class="fa-solid fa-lock mr-1"></i>
                                    Unapproved
                                </span>

                                <!-- Reporter area badge -->
                                <span
                                    v-if="r.reporter_area"
                                    class="absolute z-10 top-2 right-2 bg-green-600 text-white text-xs px-2 py-1 rounded-full shadow"
                                >
                                    {{ r.reporter_area }}
                                </span>
                            </div>
                            <div class="p-4">
                                <h3
                                    class="font-bold text-slate-800 group-hover:text-green-700 transition"
                                >
                                    {{ r.name }}
                                </h3>

                                <!--  Reporter types (replaces phone) -->
                                <div
                                    v-if="r.type_names?.length"
                                    class="flex flex-wrap gap-1 mt-2"
                                >
                                    <span
                                        v-for="(tn, i) in r.type_names"
                                        :key="i"
                                        class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full"
                                    >
                                        <i class="fa-solid fa-tag mr-1"></i
                                        >{{ tn }}
                                    </span>
                                </div>
                                <p
                                    v-else
                                    class="text-xs text-slate-400 mt-2 italic"
                                >
                                    রিপোর্টার টাইপ নেই
                                </p>

                                <!-- Location stays -->
                                <p
                                    v-if="r.location"
                                    class="text-xs text-slate-400 mt-2"
                                >
                                    <i
                                        class="fa-solid fa-location-dot mr-1"
                                    ></i>
                                    {{ r.location }}
                                </p>
                            </div>
                        </Link>
                    </div>

                    <!-- Reporter Pagination -->
                    <div
                        v-if="totalPages > 1"
                        class="flex flex-wrap justify-center items-center gap-2 mt-8"
                    >
                        <button
                            @click="changePage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-4 py-2 rounded-lg border text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-green-50"
                        >
                            Previous
                        </button>

                        <button
                            v-for="page in totalPages"
                            :key="page"
                            @click="changePage(page)"
                            class="w-10 h-10 rounded-lg border text-sm font-semibold transition"
                            :class="
                                currentPage === page
                                    ? 'bg-green-600 text-white border-green-600'
                                    : 'bg-white text-slate-600 border-slate-200 hover:bg-green-50'
                            "
                        >
                            {{ page }}
                        </button>

                        <button
                            @click="changePage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 rounded-lg border text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-green-50"
                        >
                            Next
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.filter-chip:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
    color: #00930c;
}
.filter-chip.active {
    background: linear-gradient(135deg, #00930c, #00b515);
    border-color: #00930c;
    color: #fff;
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.25);
}
.family-card,
.reporter-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 1rem;
    overflow: hidden;
    transition:
        transform 0.25s,
        box-shadow 0.25s,
        border-color 0.25s;
    display: block;
}
.family-card:hover,
.reporter-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 147, 12, 0.1);
    border-color: #86efac;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
