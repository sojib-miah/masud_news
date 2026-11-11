<script setup>
import { Head, router, usePage } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import Layout from "@/Shared/Layout.vue";
import SearchFilter from "@/Shared/SearchFilter.vue";
import Pagination from "@/Shared/Pagination.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";

const props = defineProps({
    subscriptions: { type: Object, required: true },
});

const page = usePage();

// ==================== DELETE ====================
const deletingId = ref(null);

function remove(subscription) {
    if (
        !confirm(
            `"${subscription.email}" — Are you sure. you want to delete this subscription?`,
        )
    )
        return;

    deletingId.value = subscription.id;

    router.delete(`/admin/subscriptions/${subscription.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
}

// ==================== SEARCH ====================
const search = ref(page.props.search || "");

function fetchRoles() {
    router.get(
        "/admin/subscriptions",
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function resetSearch() {
    search.value = "";
    router.get(
        "/admin/subscriptions",
        {},
        {
            preserveState: false,
            replace: true,
        },
    );
}

// Highlight matched search text
function highlight(text) {
    if (!text) return "";
    const keyword = search.value.trim();
    if (!keyword) return escapeHtml(text);

    const escapedText = escapeHtml(text);
    const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    const regex = new RegExp(`(${escapedKeyword})`, "gi");

    return escapedText.replace(
        regex,
        '<span class="bg-yellow-200 text-yellow-900 font-semibold px-0.5 rounded">$1</span>',
    );
}

// Prevent XSS since we use v-html
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// ==================== ANIMATED PLACEHOLDER ====================
const animatedPlaceholder = ref("");
const placeholderText = "Search subscriptions...";
let intervalId;

onMounted(() => {
    let i = 0;
    let typingForward = true;

    intervalId = setInterval(() => {
        if (typingForward) {
            animatedPlaceholder.value = placeholderText.slice(0, i + 1);
            i++;
            if (i === placeholderText.length) typingForward = false;
        } else {
            animatedPlaceholder.value = placeholderText.slice(0, i);
            i--;
            if (i === 0) typingForward = true;
        }
    }, 150);
});

onUnmounted(() => {
    clearInterval(intervalId);
});

// ==================== HELPERS ====================
const rowNumber = (index) => (props.subscriptions?.from ?? 1) + index;

const totalCount = () => props.subscriptions?.total ?? 0;

const initials = (email) => {
    if (!email) return "?";
    return email[0].toUpperCase();
};
</script>

<template>
    <div>
        <Head title="Subscriptions" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Layout>
            <Breadcrumb />

            <div class="min-h-screen bg-slate-50">
                <div>
                    <!-- ==================== HEADER ==================== -->
                    <div
                        class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-lg px-5 sm:px-8 py-5 sm:py-6 mb-5 sm:mb-6"
                    >
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="bg-white/20 backdrop-blur rounded-xl p-3"
                                >
                                    <i
                                        class="fa-solid fa-envelope-open-text text-white text-xl sm:text-2xl"
                                    ></i>
                                </div>
                                <div>
                                    <h1
                                        class="text-white text-xl sm:text-2xl font-bold"
                                    >
                                        Newsletter Subscriptions
                                    </h1>
                                    <p
                                        class="text-green-100 text-xs sm:text-sm mt-0.5"
                                    >
                                        সাবস্ক্রাইবারদের তালিকা ও ব্যবস্থাপনা
                                    </p>
                                </div>
                            </div>

                            <div
                                class="bg-white/15 backdrop-blur rounded-xl px-4 py-2 text-center"
                            >
                                <p
                                    class="text-green-100 text-[10px] sm:text-xs uppercase tracking-wide"
                                >
                                    Total Subscribers
                                </p>
                                <p
                                    class="text-white text-lg sm:text-2xl font-bold leading-tight"
                                >
                                    {{ totalCount() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== MAIN CARD ==================== -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                    >
                        <!-- Toolbar -->
                        <div
                            class="px-4 sm:px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4"
                        >
                            <h2
                                class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2 sm:w-1/3"
                            >
                                <i class="fa-solid fa-list text-green-600"></i>
                                All Subscriptions
                            </h2>

                            <div class="w-full sm:w-2/3">
                                <SearchFilter
                                    v-model="search"
                                    :placeholder="animatedPlaceholder"
                                    @search="fetchRoles"
                                    @reset="resetSearch"
                                />
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div
                            v-if="
                                !subscriptions.data ||
                                subscriptions.data.length === 0
                            "
                            class="p-10 sm:p-16 text-center"
                        >
                            <div
                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-3"
                            >
                                <i
                                    class="fa-solid fa-envelope-circle-check text-slate-400 text-2xl"
                                ></i>
                            </div>
                            <p class="text-slate-600 font-semibold">
                                {{
                                    search
                                        ? "কোনো সাবস্ক্রিপশন পাওয়া যায়নি"
                                        : "এখনো কোনো সাবস্ক্রাইবার নেই"
                                }}
                            </p>
                            <p class="text-sm text-slate-400 mt-1">
                                {{
                                    search
                                        ? `"${search}" এর সাথে মিলে এমন কিছু পাওয়া যায়নি`
                                        : "নতুন সাবস্ক্রাইবার এলে এখানে দেখা যাবে"
                                }}
                            </p>
                            <button
                                v-if="search"
                                @click="resetSearch"
                                class="mt-4 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Reset Search
                            </button>
                        </div>

                        <div v-else>
                            <!-- ==================== DESKTOP TABLE ==================== -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr
                                            class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wide"
                                        >
                                            <th
                                                class="px-4 py-3 text-left font-semibold w-16"
                                            >
                                                #
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left font-semibold"
                                            >
                                                Email
                                            </th>
                                            <th
                                                class="px-4 py-3 text-right font-semibold w-28"
                                            >
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="(
                                                subscription, index
                                            ) in subscriptions.data"
                                            :key="subscription.id"
                                            class="hover:bg-green-50/40 transition-colors"
                                        >
                                            <td
                                                class="px-4 py-3 text-slate-500 font-medium"
                                            >
                                                {{ rowNumber(index) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <div
                                                        class="w-9 h-9 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0 shadow-sm"
                                                    >
                                                        {{
                                                            initials(
                                                                subscription.email,
                                                            )
                                                        }}
                                                    </div>
                                                    <span
                                                        class="font-medium text-slate-800 break-all"
                                                        v-html="
                                                            highlight(
                                                                subscription.email,
                                                            )
                                                        "
                                                    ></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex justify-end">
                                                    <button
                                                        @click="
                                                            remove(subscription)
                                                        "
                                                        :disabled="
                                                            deletingId ===
                                                            subscription.id
                                                        "
                                                        class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition disabled:opacity-60 disabled:cursor-wait"
                                                        title="Delete subscription"
                                                    >
                                                        <i
                                                            :class="
                                                                deletingId ===
                                                                subscription.id
                                                                    ? 'fa-solid fa-spinner fa-spin'
                                                                    : 'fa-solid fa-trash'
                                                            "
                                                        ></i>
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ==================== MOBILE CARDS ==================== -->
                            <div class="md:hidden divide-y divide-slate-100">
                                <div
                                    v-for="(
                                        subscription, index
                                    ) in subscriptions.data"
                                    :key="subscription.id"
                                    class="p-4"
                                >
                                    <div
                                        class="flex items-start justify-between gap-3"
                                    >
                                        <div
                                            class="flex items-start gap-3 min-w-0 flex-1"
                                        >
                                            <span
                                                class="bg-green-100 text-green-700 text-xs font-bold w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                            >
                                                {{ rowNumber(index) }}
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div
                                                    class="flex items-center gap-2 mb-1"
                                                >
                                                    <div
                                                        class="w-7 h-7 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0"
                                                    >
                                                        {{
                                                            initials(
                                                                subscription.email,
                                                            )
                                                        }}
                                                    </div>
                                                    <p
                                                        class="text-[10px] uppercase text-slate-400 font-semibold tracking-wide"
                                                    >
                                                        Subscriber
                                                    </p>
                                                </div>
                                                <p
                                                    class="font-medium text-slate-800 text-sm break-all"
                                                    v-html="
                                                        highlight(
                                                            subscription.email,
                                                        )
                                                    "
                                                ></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button
                                            @click="remove(subscription)"
                                            :disabled="
                                                deletingId === subscription.id
                                            "
                                            class="w-full inline-flex items-center justify-center gap-1.5 bg-red-50 text-red-600 hover:bg-red-100 px-3 py-2 rounded-lg text-xs font-semibold transition disabled:opacity-60 disabled:cursor-wait"
                                        >
                                            <i
                                                :class="
                                                    deletingId ===
                                                    subscription.id
                                                        ? 'fa-solid fa-spinner fa-spin'
                                                        : 'fa-solid fa-trash'
                                                "
                                            ></i>
                                            Delete Subscription
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== PAGINATION ==================== -->
                            <div
                                v-if="
                                    subscriptions.links &&
                                    subscriptions.links.length > 3
                                "
                                class="px-4 sm:px-6 py-4 border-t border-slate-200"
                            >
                                <Pagination :links="subscriptions.links" />
                            </div>
                        </div>
                    </div>

                    <!-- Hint -->
                    <div
                        class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3"
                    >
                        <i
                            class="fa-solid fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"
                        ></i>
                        <p class="text-xs text-blue-700 leading-relaxed">
                            <strong>টিপস:</strong> সার্চ করলে ইমেইলের মধ্যে মিলে
                            যাওয়া অংশ হাইলাইট করা হয়। সাবস্ক্রিপশন মুছে ফেললে
                            সেই ইমেইল আর নিউজলেটার পাবে না।
                        </p>
                    </div>
                </div>
            </div>
        </Layout>
    </div>
</template>
