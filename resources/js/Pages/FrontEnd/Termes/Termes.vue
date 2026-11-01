<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { useForm } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { computed, ref } from "vue";

const props = defineProps({
    term: Object,
    categories: Array,
    subCategories: Array,
});

const form = useForm({
    email: "",
});

function submit() {
    form.post(route("subscribe"), {
        preserveScroll: true,
        onSuccess: () => form.reset("email"),
    });
}

const groupedSubCategories = computed(() => {
    const groups = {};
    props.subCategories?.forEach((sub) => {
        if (!groups[sub.category_id]) {
            groups[sub.category_id] = [];
        }
        groups[sub.category_id].push(sub);
    });
    return groups;
});

// Mobile sidebar toggle
const sidebarOpen = ref(false);
</script>

<template>
    <Layout>
        <Head title="Terms & Conditions" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div
            class="min-h-screen bg-slate-50 py-6 sm:py-8 lg:py-12 px-3 sm:px-4"
        >
            <div class="max-w-7xl mx-auto">
                <!-- Page Header -->
                <div class="mb-6 sm:mb-8">
                    <h1
                        class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-800 flex items-center gap-3"
                    >
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-700 text-white shadow-lg"
                        >
                            <i
                                class="fa-solid fa-file-contract text-lg sm:text-xl"
                            ></i>
                        </span>
                        শর্তাবলী ও নিয়মাবলী
                    </h1>
                    <p
                        class="text-sm sm:text-base text-slate-500 mt-2 ml-0 sm:ml-15"
                    >
                        আমাদের সেবা ব্যবহারের আগে শর্তাবলী ভালোভাবে পড়ে নিন
                    </p>
                </div>

                <!-- Mobile Sidebar Toggle -->
                <div class="lg:hidden mb-4">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-3 bg-white border border-slate-200 rounded-xl shadow-sm text-slate-700 font-semibold"
                    >
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-bars text-green-600"></i>
                            বিভাগ ও নিউজলেটার
                        </span>
                        <i
                            :class="
                                sidebarOpen
                                    ? 'fa-solid fa-chevron-up'
                                    : 'fa-solid fa-chevron-down'
                            "
                            class="text-slate-400 text-sm transition-transform"
                        ></i>
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- ==================== MAIN CONTENT ==================== -->
                    <div class="w-full lg:w-2/3 order-2 lg:order-1">
                        <div
                            class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                        >
                            <div
                                class="px-5 sm:px-8 py-4 border-b border-slate-200 bg-gradient-to-r from-green-50 to-white"
                            >
                                <h2
                                    class="text-lg sm:text-xl font-bold text-green-800 flex items-center gap-2"
                                >
                                    <i
                                        class="fa-solid fa-book-open text-green-600"
                                    ></i>
                                    শর্তাবলী
                                </h2>
                            </div>

                            <div
                                class="p-5 sm:p-8 prose prose-slate max-w-none term-content"
                            >
                                <div
                                    v-if="term?.termes"
                                    v-html="term.termes"
                                ></div>
                                <div
                                    v-else
                                    class="text-center py-12 text-slate-400"
                                >
                                    <i
                                        class="fa-solid fa-inbox text-4xl mb-3 block"
                                    ></i>
                                    <p>কোনো শর্তাবলী পাওয়া যায়নি</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SIDEBAR ==================== -->
                    <aside
                        style="background-color: transparent"
                        class="w-full lg:w-1/3 order-1 lg:order-2"
                        :class="sidebarOpen ? 'block' : 'hidden lg:block'"
                    >
                        <div class="lg:sticky lg:top-6 space-y-6">
                            <!-- Categories Card -->
                            <div
                                class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
                            >
                                <div
                                    class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-green-50 to-white"
                                >
                                    <h3
                                        class="text-base sm:text-lg font-bold text-green-800 flex items-center gap-2"
                                    >
                                        <i
                                            class="fa-solid fa-folder-tree text-green-600"
                                        ></i>
                                        বিভাগসমূহ
                                    </h3>
                                </div>

                                <ul
                                    class="p-3 sm:p-4 space-y-1 max-h-[60vh] overflow-y-auto custom-scroll"
                                >
                                    <li
                                        v-for="category in categories"
                                        :key="category.id"
                                        class="rounded-lg"
                                    >
                                        <Link
                                            :href="
                                                route('all.news', [
                                                    category.slug,
                                                ])
                                            "
                                            class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-slate-700 hover:bg-green-50 hover:text-green-700 transition-colors group"
                                        >
                                            <span
                                                class="font-semibold text-sm truncate"
                                            >
                                                {{ category.name }}
                                            </span>
                                            <span
                                                class="flex-shrink-0 text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 group-hover:bg-green-100 group-hover:text-green-700 transition-colors"
                                            >
                                                {{ category.news_count }}
                                            </span>
                                        </Link>

                                        <!-- Subcategories -->
                                        <ul
                                            v-if="
                                                groupedSubCategories[
                                                    category.id
                                                ]
                                            "
                                            class="ml-3 pl-3 border-l-2 border-green-100 mt-1 space-y-0.5"
                                        >
                                            <li
                                                v-for="sub in groupedSubCategories[
                                                    category.id
                                                ]"
                                                :key="sub.id"
                                            >
                                                <Link
                                                    :href="
                                                        route('all.news', [
                                                            category.slug,
                                                            sub.slug,
                                                        ])
                                                    "
                                                    class="flex items-center gap-2 px-2 py-1.5 rounded-md text-xs sm:text-sm text-slate-500 hover:text-green-700 hover:bg-green-50 transition-colors"
                                                >
                                                    <i
                                                        class="fa-solid fa-angle-right text-[10px] text-green-400"
                                                    ></i>
                                                    <span class="truncate">{{
                                                        sub.name
                                                    }}</span>
                                                </Link>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>

                            <!-- Newsletter Card -->
                            <div
                                class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl shadow-lg overflow-hidden text-white"
                            >
                                <div class="p-5 sm:p-6">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i
                                            class="fa-solid fa-envelope-open-text text-xl"
                                        ></i>
                                        <h3
                                            class="text-base sm:text-lg font-bold"
                                        >
                                            নিউজলেটার
                                        </h3>
                                    </div>
                                    <p
                                        class="text-xs sm:text-sm text-green-50 mb-4 leading-relaxed"
                                    >
                                        সর্বশেষ সংবাদ ও আপডেট পেতে আমাদের
                                        নিউজলেটার সাবস্ক্রাইব করুন।
                                    </p>

                                    <form
                                        @submit.prevent="submit"
                                        class="space-y-3"
                                    >
                                        <div class="relative">
                                            <i
                                                class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-green-300 text-sm"
                                            ></i>
                                            <input
                                                type="tel"
                                                v-model="form.email"
                                                class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-white/15 border border-white/25 placeholder-green-100 text-white text-sm focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-white/20 transition"
                                                placeholder="আপনার ফোন নম্বর লিখুন"
                                                required
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            class="w-full px-4 py-2.5 bg-white text-green-700 font-bold rounded-lg hover:bg-green-50 transition disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm"
                                            :disabled="form.processing"
                                        >
                                            <i
                                                v-if="form.processing"
                                                class="fa-solid fa-spinner fa-spin"
                                            ></i>
                                            <i
                                                v-else
                                                class="fa-solid fa-paper-plane"
                                            ></i>
                                            {{
                                                form.processing
                                                    ? "প্রেরণ হচ্ছে..."
                                                    : "সাবস্ক্রাইব করুন"
                                            }}
                                        </button>
                                        <div
                                            v-if="form.errors.email"
                                            class="text-red-100 bg-red-500/30 border border-red-300/50 rounded-lg px-3 py-2 text-xs"
                                        >
                                            <i
                                                class="fa-solid fa-circle-exclamation mr-1"
                                            ></i>
                                            {{ form.errors.email }}
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
aside {
    background-color: transparent !important;
}
/* ===== Term content typography ===== */
.term-content :deep(h1),
.term-content :deep(h2),
.term-content :deep(h3),
.term-content :deep(h4),
.term-content :deep(h5),
.term-content :deep(h6) {
    color: #14532d;
    font-weight: 700;
    margin-top: 1.5em;
    margin-bottom: 0.75em;
    line-height: 1.3;
}

.term-content :deep(h1) {
    font-size: 1.5rem;
}
.term-content :deep(h2) {
    font-size: 1.3rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dcfce7;
}
.term-content :deep(h3) {
    font-size: 1.1rem;
}

.term-content :deep(p) {
    color: #334155;
    line-height: 1.75;
    margin-bottom: 1em;
    font-size: 0.95rem;
}

.term-content :deep(ul),
.term-content :deep(ol) {
    margin-bottom: 1em;
    padding-left: 1.5em;
}

.term-content :deep(ul) {
    list-style: disc;
}
.term-content :deep(ol) {
    list-style: decimal;
}

.term-content :deep(li) {
    color: #334155;
    line-height: 1.7;
    margin-bottom: 0.4em;
    font-size: 0.95rem;
}

.term-content :deep(a) {
    color: #059669;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.term-content :deep(a:hover) {
    color: #047857;
}

.term-content :deep(strong) {
    color: #14532d;
}

.term-content :deep(blockquote) {
    border-left: 4px solid #10b981;
    background: #f0fdf4;
    padding: 0.75rem 1rem;
    margin: 1em 0;
    border-radius: 0 0.5rem 0.5rem 0;
    color: #14532d;
    font-style: italic;
}

.term-content :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1em;
    font-size: 0.9rem;
}
.term-content :deep(th),
.term-content :deep(td) {
    border: 1px solid #e2e8f0;
    padding: 0.5rem 0.75rem;
    text-align: left;
}
.term-content :deep(th) {
    background: #f0fdf4;
    color: #14532d;
    font-weight: 600;
}

/* ===== Custom scrollbar ===== */
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* ===== Responsive adjustments ===== */
@media (max-width: 640px) {
    .term-content :deep(h1) {
        font-size: 1.25rem;
    }
    .term-content :deep(h2) {
        font-size: 1.1rem;
    }
    .term-content :deep(h3) {
        font-size: 1rem;
    }
    .term-content :deep(p),
    .term-content :deep(li) {
        font-size: 0.9rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
    }
}
</style>
