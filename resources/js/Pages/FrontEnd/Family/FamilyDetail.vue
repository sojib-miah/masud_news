<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { route } from "ziggy-js";

const props = defineProps({
    family: { type: Object, required: true },
});
</script>

<template>
    <Layout>
        <Head :title="props.family.name" />

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-3 sm:px-4 lg:px-6">
                <!-- Back -->
                <Link
                    :href="route('our.family')"
                    class="inline-flex items-center text-green-700 hover:text-green-800 text-sm font-semibold mb-4"
                >
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    সব ফ্যামিলিতে ফিরে যান
                </Link>

                <div
                    class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden"
                >
                    <!-- Header banner -->
                    <div
                        class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-6"
                    >
                        <h1 class="text-2xl font-bold text-white">
                            {{ props.family.name }}
                        </h1>
                        <p
                            v-if="props.family.designation"
                            class="text-green-100 text-sm mt-1"
                        >
                            <i class="fa-solid fa-briefcase mr-1"></i>
                            {{ props.family.designation }}
                        </p>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Image -->
                        <div class="md:col-span-1">
                            <img
                                v-if="props.family.image_url"
                                :src="props.family.image_url"
                                :alt="props.family.name"
                                @error="
                                    (e) =>
                                        (e.target.src = '/images/default.jpg')
                                "
                                class="w-full rounded-xl border border-slate-200 object-cover shadow"
                            />
                            <div
                                v-else
                                class="w-full aspect-square bg-green-50 rounded-xl border border-green-200 flex items-center justify-center text-green-700 text-6xl font-bold"
                            >
                                {{ (props.family.name || "?").charAt(0) }}
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="md:col-span-2 space-y-4">
                            <div v-if="props.family.short_desc">
                                <h3
                                    class="text-sm font-bold text-green-700 uppercase mb-1"
                                >
                                    সংক্ষিপ্ত বিবরণ
                                </h3>
                                <p class="text-slate-600 text-sm">
                                    {{ props.family.short_desc }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div
                                    v-if="props.family.phone"
                                    class="flex items-start gap-2 p-3 bg-slate-50 rounded-lg"
                                >
                                    <i
                                        class="fa-solid fa-phone text-green-600 mt-1"
                                    ></i>
                                    <div>
                                        <p class="text-xs text-slate-400">
                                            ফোন
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-slate-700"
                                        >
                                            {{ props.family.phone }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.family.email"
                                    class="flex items-start gap-2 p-3 bg-slate-50 rounded-lg"
                                >
                                    <i
                                        class="fa-solid fa-envelope text-green-600 mt-1"
                                    ></i>
                                    <div>
                                        <p class="text-xs text-slate-400">
                                            ইমেইল
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-slate-700 break-all"
                                        >
                                            {{ props.family.email }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.family.location"
                                    class="flex items-start gap-2 p-3 bg-slate-50 rounded-lg"
                                >
                                    <i
                                        class="fa-solid fa-location-dot text-green-600 mt-1"
                                    ></i>
                                    <div>
                                        <p class="text-xs text-slate-400">
                                            লোকেশন
                                        </p>
                                        <p
                                            class="text-sm font-semibold text-slate-700"
                                        >
                                            {{ props.family.location }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Long desc -->
                    <div
                        v-if="props.family.long_desc"
                        class="px-6 pb-6 border-t border-slate-100 pt-4"
                    >
                        <h3
                            class="text-sm font-bold text-green-700 uppercase mb-2"
                        >
                            বিস্তারিত
                        </h3>
                        <p class="text-slate-700 text-sm whitespace-pre-line">
                            {{ props.family.long_desc }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
