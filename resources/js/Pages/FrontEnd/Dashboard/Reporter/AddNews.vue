<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import { route } from "ziggy-js";
</script>

<template>
    <Layout>
        <Head title="Reporter News" />
        <div class="flex justify-between items-center py-6">
            <div class="text-xl">
                All News
                <Breadcrumb />
            </div>
            <div>
                <Link :href="route('news.all')" class="btn rounded">All News</Link>
            </div>
        </div>
        <div class="flex gap-3 py-5">
            <Link
                :href="route('news')"
                class="w-1/3 border border-slate-300 shadow-lg rounded flex justify-center items-center bg-white"
            >
                <div class="text-center p-7">
                    <i
                        class="fa-regular fa-newspaper text-4xl font-bold mb-3"
                    ></i>
                    <h2 class="text-xl font-bold">News</h2>
                    <p>Add News With Image</p>
                </div>
            </Link>
            <Link
                :href="route('audio.news')"
                class="w-1/3 border border-slate-300 shadow-lg rounded flex justify-center items-center bg-white"
            >
                <div class="text-center p-7">
                    <i
                        class="fa-solid fa-microphone-lines text-4xl font-bold mb-3"
                    ></i>
                    <h2 class="text-xl font-bold">Audio News</h2>
                    <p>Upload Audio News</p>
                </div>
            </Link>
            <Link
                :href="route('video.news')"
                class="w-1/3 border border-slate-300 shadow-lg rounded flex justify-center items-center bg-white"
            >
                <div class="text-center p-7">
                    <i class="fa-solid fa-video text-4xl font-bold mb-3"></i>
                    <h2 class="text-xl font-bold">Video News</h2>
                    <p>Upload Video News</p>
                </div>
            </Link>
        </div>
    </Layout>
</template>
