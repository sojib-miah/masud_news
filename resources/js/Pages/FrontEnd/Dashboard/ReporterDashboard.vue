<script setup>
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AlertModal from "@/Shared/AlertModal.vue";
import { route } from "ziggy-js";
import { computed, ref, watch } from "vue";

const page = usePage();
const stats = computed(() => page.props.stats || {});

const modal = ref({
    show: false,
    type: "error",
    title: "",
    message: "",
});

watch(
    () => page.props.flash?.modal,
    (data) => {
        if (data) {
            modal.value = {
                show: true,
                type: data.type ?? "error",
                title: data.title ?? "",
                message: data.message ?? "",
            };
        }
    },
    { immediate: true }, // show even if redirect lands with flash present
);

function closeModal() {
    modal.value.show = false;

    // Clear flash so it won't show again on refresh/back navigation
    router.reload({
        preserveScroll: true,
        preserveState: true,
        only: [],
        onSuccess: () => {
            // optional
        },
    });
}

const showPendingAlert = ref(true);
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
        <AlertModal
            :show="modal.show"
            :type="modal.type"
            :title="modal.title"
            :message="modal.message"
            @close="closeModal"
        />

        <div class="font-robo pb-10">
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
                    <i class="fa-solid fa-plus"></i> Add New News
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
                            Total News
                        </p>
                        <h2 class="text-4xl font-bold text-slate-800 mb-4">
                            {{ stats.total_news || 0 }}
                        </h2>
                        <Link
                            :href="route('news.all')"
                            class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1"
                        >
                            View All
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
                            Pending News
                        </p>
                        <h2 class="text-4xl font-bold text-slate-800 mb-4">
                            {{ stats.pending_news || 0 }}
                        </h2>
                        <Link
                            :href="route('pending.news')"
                            class="text-xs font-bold text-yellow-600 hover:text-yellow-700 flex items-center gap-1"
                        >
                            View All
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
                            Published News
                        </p>
                        <h2 class="text-4xl font-bold text-slate-800 mb-4">
                            {{ stats.published_news || 0 }}
                        </h2>
                        <Link
                            :href="route('news.all')"
                            class="text-xs font-bold text-orange-600 hover:text-orange-700 flex items-center gap-1"
                        >
                            View All
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
                            Total Income
                        </p>
                        <h2
                            class="text-3xl sm:text-4xl font-bold text-slate-800 mb-4"
                        >
                            ৳{{ stats.reporter_income || 0 }}
                        </h2>
                        <span
                            class="text-xs font-bold text-blue-600 flex items-center gap-1"
                        >
                            <i class="fa-solid fa-chart-line"></i> Lifetime
                            Income
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
                                <h3 class="text-lg font-bold">Your Balance</h3>
                            </div>
                            <h2 class="text-4xl sm:text-5xl font-bold mt-2">
                                ৳{{ stats.total_balance || 0 }}
                            </h2>
                            <p class="text-green-100 text-sm mt-2">
                                Amount available for immediate withdrawal
                            </p>
                        </div>

                        <div
                            class="flex flex-row sm:flex-col gap-4 w-full sm:w-auto bg-white/10 p-4 rounded-xl border border-white/20"
                        >
                            <div class="flex-1 sm:flex-none">
                                <p class="text-xs text-green-200 mb-0.5">
                                    Total Income
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
                                    Total Withdrawals
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
                        Quick Actions
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
                                    >Draft News</span
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
                                    >Scheduled News</span
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
                                    >Press ID Card</span
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

        <!-- pending news alert  -->
        <Transition name="pending-alert">
            <div
                v-if="Number(stats.pending_news) > 0 && showPendingAlert"
                class="pending-news-alert"
                role="alert"
                aria-live="polite"
            >
                <button
                    type="button"
                    class="pending-alert-close"
                    aria-label="Close alert"
                    @click="showPendingAlert = false"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="pending-alert-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <div class="pending-alert-content">
                    <strong>Pending News Alert!</strong>

                    <p>
                        আপনার
                        <strong>{{ stats.pending_news }}</strong>
                        টি নিউজ Pending রয়েছে। নিউজ Active করতে Admin-এর সাথে
                        যোগাযোগ করুন। 01320991356
                    </p>

                    <Link
                        :href="route('pending.news')"
                        class="pending-alert-link"
                    >
                        Pending News দেখুন
                        <i class="fa-solid fa-arrow-right"></i>
                    </Link>
                </div>
            </div>
        </Transition>
    </Layout>
</template>

<style scoped>
.pending-news-alert {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 9999;

    display: flex;
    align-items: flex-start;
    gap: 14px;

    width: min(410px, calc(100vw - 32px));
    padding: 22px 20px;

    background: #fff;
    border: 1px solid #fde68a;
    border-left: 5px solid #f59e0b;
    border-radius: 16px;

    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.16);
}

.pending-alert-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;

    width: 44px;
    height: 44px;
    border-radius: 12px;

    color: #b45309;
    background: #fef3c7;
    font-size: 20px;
}

.pending-alert-content {
    padding-right: 12px;
}

.pending-alert-content > strong {
    display: block;
    margin-bottom: 6px;
    color: #92400e;
    font-size: 16px;
}

.pending-alert-content p {
    margin: 0;
    color: #475569;
    font-size: 13px;
    line-height: 1.8;
}

.pending-alert-close {
    position: absolute;
    top: 10px;
    right: 10px;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 28px;
    height: 28px;
    border-radius: 8px;

    color: #64748b;
    background: #f8fafc;
    cursor: pointer;
}

.pending-alert-close:hover {
    color: #b91c1c;
    background: #fee2e2;
}

.pending-alert-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    margin-top: 12px;
    color: #b45309;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.pending-alert-link:hover {
    color: #92400e;
    text-decoration: underline;
}

.pending-alert-enter-active,
.pending-alert-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.pending-alert-enter-from,
.pending-alert-leave-to {
    opacity: 0;
    transform: translateY(16px);
}

@media (max-width: 480px) {
    .pending-news-alert {
        right: 12px;
        bottom: 12px;
        width: calc(100vw - 24px);
    }
}
</style>
