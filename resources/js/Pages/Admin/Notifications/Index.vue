<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";
import { computed } from "vue";

const props = defineProps({
    notifications: Object,
    unread_count: Number,
});

const items = computed(() => props.notifications?.data ?? []);

function formatTime(iso) {
    if (!iso) return "";
    const d = new Date(iso);
    const diff = Math.floor((Date.now() - d.getTime()) / 60000);
    if (diff < 1) return "এইমাত্র";
    if (diff < 60) return `${diff} মিনিট আগে`;
    const h = Math.floor(diff / 60);
    if (h < 24) return `${h} ঘণ্টা আগে`;
    const day = Math.floor(h / 24);
    if (day < 7) return `${day} দিন আগে`;
    return d.toLocaleDateString("bn-BD");
}

function openNotification(n) {
    router.visit(route("notifications.read", n.id));
}

function markAllRead() {
    router.post(
        route("notifications.markAllRead"),
        {},
        { preserveScroll: true },
    );
}

function deleteNotification(n, e) {
    e.stopPropagation();
    if (!confirm("এই নোটিফিকেশন মুছে ফেলবেন?")) return;
    router.delete(route("notifications.destroy", n.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Notifications" />
    <Layout>
        <Breadcrumb />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
        >
            <div
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-bell mr-1"></i> Notifications
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        আপনার নোটিফিকেশন
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        {{ unread_count }} টি অপঠিত
                    </p>
                </div>
                <button
                    v-if="unread_count > 0"
                    @click="markAllRead"
                    class="inline-flex items-center gap-2 rounded-full bg-white/10 hover:bg-white/20 px-4 py-2 text-white backdrop-blur ring-1 ring-white/20 text-xs font-semibold transition"
                >
                    <i class="fa-solid fa-check-double"></i>
                    সব পড়া হিসেবে চিহ্নিত করুন
                </button>
            </div>
        </div>

        <div
            class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-md overflow-hidden"
        >
            <div
                v-if="items.length === 0"
                class="p-10 text-center text-slate-500"
            >
                <i
                    class="fa-regular fa-bell-slash text-4xl mb-3 text-slate-300"
                ></i>
                <p>কোনো নোটিফিকেশন নেই।</p>
            </div>

            <ul v-else class="divide-y divide-slate-100">
                <li
                    v-for="n in items"
                    :key="n.id"
                    @click="openNotification(n)"
                    class="flex items-start gap-3 p-4 hover:bg-slate-50 transition cursor-pointer group"
                    :class="{ 'bg-blue-50/50': !n.read_at }"
                >
                    <div
                        class="flex-shrink-0 h-11 w-11 rounded-full flex items-center justify-center shadow"
                        :class="
                            n.data?.type === 'news_stored'
                                ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white'
                                : 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white'
                        "
                    >
                        <i :class="`fa-solid ${n.data?.icon ?? 'fa-bell'}`"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3
                                class="text-sm font-bold text-slate-800 truncate"
                            >
                                {{ n.data?.title ?? "Notification" }}
                            </h3>
                            <span
                                v-if="!n.read_at"
                                class="inline-block h-2 w-2 rounded-full bg-blue-500 flex-shrink-0"
                            ></span>
                        </div>
                        <p class="text-sm text-slate-600 mt-0.5 line-clamp-2">
                            {{ n.data?.message }}
                        </p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="text-[11px] text-slate-400">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ formatTime(n.created_at) }}
                            </span>
                            <Link
                                v-if="n.data?.url"
                                :href="n.data.url"
                                class="text-[11px] text-blue-600 hover:underline"
                                @click.stop
                            >
                                বিস্তারিত →
                            </Link>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-red-500 transition"
                        @click="deleteNotification(n, $event)"
                        title="Delete"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </li>
            </ul>
        </div>

        <div
            v-if="notifications?.links?.length > 3"
            class="flex justify-center gap-1 mt-6"
        >
            <template v-for="link in notifications.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 hover:bg-slate-50"
                    :class="{
                        'bg-green-600 text-white border-green-600': link.active,
                    }"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="px-3 py-1.5 text-sm text-slate-400 border border-slate-200 rounded-lg"
                    v-html="link.label"
                />
            </template>
        </div>
    </Layout>
</template>
