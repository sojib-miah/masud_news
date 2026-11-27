<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const page = usePage();
const count = ref(0);
const userId = page.props.auth?.user?.id;
let channel = null;

onMounted(() => {
    if (!userId) return;

    // initial unread count (page props থেকে optional)
    count.value = page.props.unreadCount ?? 0;

    channel = window.Echo.private(`notifications.${userId}`).listen(
        "NewNotification",
        (e) => {
            count.value += 1;
            // optional: browser notification
            if (Notification.permission === "granted") {
                new Notification(e.title ?? "নতুন নোটিফিকেশন", {
                    body: e.message ?? "",
                    icon: "/favicon.ico",
                });
            }
        },
    );
});

onBeforeUnmount(() => {
    if (channel && userId) {
        window.Echo.leave(`notifications.${userId}`);
    }
});

const goToNotifications = () => {
    count.value = 0;
    router.visit(route("notifications.index"));
};
</script>

<template>
    <div class="inline-block">
        <button
            type="button"
            class="relative p-2 rounded-full hover:bg-white/10 transition"
            @click="goToNotifications"
        >
            <i class="fa-solid fa-bell text-2xl"></i>
            <span
                v-if="count > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold h-5 min-w-[20px] px-1 rounded-full flex items-center justify-center ring-2 ring-white"
            >
                {{ count > 9 ? "9+" : count }}
            </span>
        </button>
    </div>
</template>
