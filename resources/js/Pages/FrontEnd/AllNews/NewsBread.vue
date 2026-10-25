<script setup>
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const category = page.props.category;
const subcategory = page.props.subcategory;
</script>

<template>
    <div
        class="flex items-center gap-3 p-2 bg-slate-200 border-b border-b-black pb-3"
    >
        <Link :href="route('home')" class="text-blue-500">
            <i class="fa-regular fa-house"></i>
        </Link>

        <i class="fa-solid fa-angle-right"></i>

        <Link :href="route('all.news', category.slug)">
            {{ category.name }}
        </Link>

        <template v-if="subcategory">
            <i class="fa-solid fa-angle-right"></i>
            <span>{{ subcategory.name }}</span>
        </template>
    </div>
</template>
