<script setup>
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed } from "vue";

const page = usePage();
const stats = computed(() => page.props.stats || {});
</script>

<template>
    <Head title="Reporter Dashboard" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <FlashMessage
        v-if="$page.props.flash?.error"
        :message="$page.props.flash.error"
        type="error"
    />

    <Layout>
        <div class="max-w-7xl mx-auto font-robo pb-10">
            <!-- ==================== WELCOME HEADER ==================== -->
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8"
            >
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">
                        স্বাগতম, {{ $page.props.auth.user.name }}!
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">
                        আপনার রিপোর্টিং পরিসংখ্যান এবং আয়ের সারসংক্ষেপ এখানে
                        দেখুন।
                    </p>
                </div>

                <Link
                    :href="route('news')"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-green-600/30 transition-all hover:-translate-y-0.5"
                >
                    <i class="fa-solid fa-plus"></i> নতুন সংবাদ যুক্ত করুন
                </Link>
            </div>

            <!-- ==================== STATS GRID ==================== -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 mb-8"
            >
                <!-- Total News -->
                <div
                    class="relative bg-white rounded-2xl shadow-sm border border-slate-200 p-5 overflow-hidden group hover:shadow-md transition-shadow"
                >
                    <div
                        class="absolute -right-6 -top-6 text-teal-50 opacity-50 group-hover:scale-110 transition-transform duration-500"
                    >
                        <i class="fa-solid fa-newspaper text-8xl"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm font-semibold text-slate-500 mb-1">
                            মোট সংবাদ
                        </p>
                        <h2 class="text-4xl font-bold text-slate-800 mb-4">
                            {{ stats.total_news || 0 }}
                        </h2>
                        <Link
                            :href="route('news.all')"
                            class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1"
                        >
                            সবগুলো দেখুন
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                </div>

                <!-- Pending News -->
                <div
                    class="relative bg-white rounded-2xl shadow-sm border border-slate-200 p-5 overflow-hidden group hover:shadow-md transition-shadow"
                >
                    <div
                        class="absolute -right-6 -top-6 text-yellow-50 opacity-50 group-hover:scale-110 transition-transform duration-500"
                    >
                        <i class="fa-solid fa-clock text-8xl"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm font-semibold text-slate-500 mb-1">
                            অপেক্ষমাণ সংবাদ
                        </p>
                        <h2 class="text-4xl font-bold text-slate-800 mb-4">
                            {{ stats.pending_news || 0 }}
                        </h2>
                        <Link
                            :href="route('pending.news')"
                            class="text-xs font-bold text-yellow-600 hover:text-yellow-700 flex items-center gap-1"
                        >
                            সবগুলো দেখুন
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                </div>

                <!-- Published News -->
                <div
                    class="relative bg-white rounded-2xl shadow-sm border border-slate-200 p-5 overflow-hidden group hover:shadow-md transition-shadow"
                >
                    <div
                        class="absolute -right-6 -top-6 text-orange-50 opacity-50 group-hover:scale-110 transition-transform duration-500"
                    >
                        <i class="fa-solid fa-check-double text-8xl"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm font-semibold text-slate-500 mb-1">
                            প্রকাশিত সংবাদ
                        </p>
                        <h2 class="text-4xl font-bold text-slate-800 mb-4">
                            {{ stats.published_news || 0 }}
                        </h2>
                        <Link
                            :href="route('news.all')"
                            class="text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1"
                        >
                            সবগুলো দেখুন
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                </div>

                <!-- Total Income -->
                <div
                    class="relative bg-white rounded-2xl shadow-sm border border-slate-200 p-5 overflow-hidden group hover:shadow-md transition-shadow"
                >
                    <div
                        class="absolute -right-6 -top-6 text-blue-50 opacity-50 group-hover:scale-110 transition-transform duration-500"
                    >
                        <i class="fa-solid fa-money-bill-wave text-8xl"></i>
                    </div>
                    <div class="relative z-10">
                        <p class="text-sm font-semibold text-slate-500 mb-1">
                            মোট আয়
                        </p>
                        <h2
                            class="text-3xl sm:text-4xl font-bold text-slate-800 mb-4"
                        >
                            ৳{{ stats.reporter_income || 0 }}
                        </h2>
                        <span
                            class="text-xs font-bold text-blue-600 flex items-center gap-1"
                        >
                            <i class="fa-solid fa-chart-line"></i> আজীবন আয়
                        </span>
                    </div>
                </div>
            </div>

            <!-- ==================== SECONDARY SECTION (WALLET & QUICK LINKS) ==================== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Wallet Summary -->
                <div
                    class="lg:col-span-2 bg-gradient-to-br from-green-600 to-green-800 rounded-2xl shadow-lg p-6 sm:p-8 text-white relative overflow-hidden"
                >
                    <!-- Background Pattern -->
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-20 -mt-20"
                    ></div>
                    <div
                        class="absolute bottom-0 right-32 w-32 h-32 bg-green-400/20 rounded-full blur-2xl"
                    ></div>

                    <div
                        class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6"
                    >
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="p-2 bg-white/20 rounded-lg"
                                    ><i class="fa-solid fa-wallet text-xl"></i
                                ></span>
                                <h3 class="text-lg font-bold">
                                    আপনার ব্যালেন্স
                                </h3>
                            </div>
                            <h2 class="text-4xl sm:text-5xl font-bold mt-2">
                                ৳{{ stats.total_balance || 0 }}
                            </h2>
                            <p class="text-green-100 text-sm mt-2">
                                অবিলম্বে উত্তোলনযোগ্য পরিমাণ
                            </p>
                        </div>

                        <div
                            class="flex flex-row sm:flex-col gap-4 w-full sm:w-auto bg-white/10 p-4 rounded-xl border border-white/20"
                        >
                            <div class="flex-1 sm:flex-none">
                                <p class="text-xs text-green-200 mb-0.5">
                                    মোট আয়
                                </p>
                                <p class="font-bold text-lg">
                                    ৳{{ stats.reporter_income || 0 }}
                                </p>
                            </div>
                            <div
                                class="w-px h-full bg-white/20 sm:hidden"
                            ></div>
                            <div class="flex-1 sm:flex-none">
                                <p class="text-xs text-green-200 mb-0.5">
                                    মোট উত্তোলন
                                </p>
                                <p class="font-bold text-lg">
                                    ৳{{ stats.total_withdraw || 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6"
                >
                    <h3
                        class="text-lg font-bold text-slate-800 mb-4 pb-3 border-b border-slate-100"
                    >
                        কুইক অ্যাকশন
                    </h3>
                    <div class="space-y-2">
                        <Link
                            :href="route('draft.news')"
                            class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-slate-200 group-hover:text-slate-700 transition-colors"
                                >
                                    <i class="fa-solid fa-file-pen text-sm"></i>
                                </span>
                                <span
                                    class="font-semibold text-slate-700 text-sm"
                                    >ড্রাফট সংবাদ</span
                                >
                            </div>
                            <i
                                class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"
                            ></i>
                        </Link>

                        <Link
                            :href="route('schedule.news')"
                            class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-slate-200 group-hover:text-slate-700 transition-colors"
                                >
                                    <i class="fa-regular fa-clock text-sm"></i>
                                </span>
                                <span
                                    class="font-semibold text-slate-700 text-sm"
                                    >শিডিউল সংবাদ</span
                                >
                            </div>
                            <i
                                class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"
                            ></i>
                        </Link>

                        <Link
                            :href="route('id.card')"
                            class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition-colors group"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-slate-200 group-hover:text-slate-700 transition-colors"
                                >
                                    <i
                                        class="fa-regular fa-id-badge text-sm"
                                    ></i>
                                </span>
                                <span
                                    class="font-semibold text-slate-700 text-sm"
                                    >প্রেস আইডি কার্ড</span
                                >
                            </div>
                            <i
                                class="fa-solid fa-chevron-right text-slate-300 text-xs group-hover:text-slate-500 transition-colors"
                            ></i>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
