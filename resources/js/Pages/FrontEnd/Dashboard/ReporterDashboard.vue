<script setup>
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";
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
        <div class="flex justify-between items-center mb-4">
            <Breadcrumb />
            <Link :href="route('news')" class="btn rounded">
                <i class="fa-solid fa-plus"></i> Add News
            </Link>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                class="relative one rounded-lg shadow-lg overflow-hidden text-white"
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total News</p>
                        <h2 class="text-4xl font-bold mt-2 pb-3">
                            {{ $page.props.stats.total_news }}
                        </h2>
                        <Link
                            :href="route('news.all')"
                            class="border rounded p-2 bg-white text-black border-white font-bold"
                            type="button"
                        >
                            View All
                        </Link>
                    </div>
                    <i class="fa-solid fa-newspaper text-3xl opacity-90"></i>
                </div>
            </div>

            <div
                class="relative two rounded-lg shadow-lg overflow-hidden text-white"
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Pending News</p>
                        <h2 class="text-4xl font-bold mt-2 pb-3">
                            {{ $page.props.stats.pending_news }}
                        </h2>
                        <Link
                            :href="route('pending.news')"
                            class="border rounded p-2 bg-white text-black border-white font-bold"
                            type="button"
                        >
                            View All
                        </Link>
                    </div>
                    <i class="fa-solid fa-clock text-3xl opacity-90"></i>
                </div>
            </div>

            <div
                class="relative three rounded-lg shadow-lg overflow-hidden text-white"
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Published News</p>
                        <h2 class="text-4xl font-bold mt-2 pb-3">
                            {{ $page.props.stats.published_news }}
                        </h2>
                        <Link
                            :href="route('news.all')"
                            class="border rounded p-2 bg-white text-black border-white font-bold"
                            type="button"
                        >
                            View All
                        </Link>
                    </div>
                    <i class="fa-solid fa-newspaper text-3xl opacity-90"></i>
                </div>
            </div>

            <div
                class="relative four rounded-lg shadow-lg overflow-hidden text-white"
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Income</p>
                        <h2 class="text-4xl font-bold mt-2 pb-3">
                            {{ $page.props.stats.reporter_income }} BDT
                        </h2>
                    </div>
                    <i class="fa-solid fa-money-bill text-3xl opacity-90"></i>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.one {
    background: linear-gradient(135deg, #14b8a6 50%, #0f9f95 50%);
}
.two {
    background: linear-gradient(135deg, #facc15 50%, #eab308 50%);
}
.three {
    background: linear-gradient(135deg, #f97316 50%, #ea580c 50%);
}
.four {
    background: linear-gradient(135deg, #3b82f6 50%, #2563eb 50%);
}
h2 {
    margin-bottom: 2px !important;
}
.slide-card-enter-active,
.slide-card-leave-active {
    transition: transform 0.35s ease;
}
.slide-card-enter-from,
.slide-card-leave-to {
    transform: translateX(100%);
}

.slide-btn-enter-active,
.slide-btn-leave-active {
    transition: opacity 0.3s ease;
}
.slide-btn-enter-from,
.slide-btn-leave-to {
    opacity: 0;
}
</style>
