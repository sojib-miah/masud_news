<script setup>
import Layout from "@/Components/FrontEnd/Motamot/Layout.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { Head, Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";

const props = defineProps({
    role: String,
    motamots: Object,
});
</script>

<template>
    <Head title="Motamot Dashboard" />
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />
    <Layout>
        <Breadcrumb />
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                class="relative rounded-lg shadow-lg overflow-hidden text-white"
                style="
                    background: linear-gradient(
                        135deg,
                        #14b8a6 50%,
                        #0f9f95 50%
                    );
                "
            >
                <div class="p-6 flex justify-between items-center">
                    <div>
                        <p class="text-sm opacity-90">Total Motamots</p>
                        <h2 class="text-4xl font-bold mt-2 pb-3">
                            {{ props.motamots.total }}
                        </h2>
                        <Link
                            :href="route('motamot.index')"
                            class="border rounded p-2 bg-white text-black border-white font-bold"
                            type="button"
                        >
                            View All
                        </Link>
                    </div>
                    <i class="fa-solid fa-newspaper text-3xl opacity-90"></i>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
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
