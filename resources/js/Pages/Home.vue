<script setup>
import Layout from "@/Components/Layout.vue";
import { Head } from "@inertiajs/vue3";
import Home from "@/Components/FrontEnd/Home/Home.vue";
defineProps({
    divisions: Array,
    homeCategories: Array,
    rajcategories: Array,
    techCategories: Array,
    subCategories: Array,
    sompadokiyos: Array,
    subCategory: Object,
    mainCategories: Array,
    sarades: Object,
    bivag: Object,
    video: Array,
    allNews: Array,
});
</script>

<template>
    <Layout>
        <Head title="Home Page" />
        <Home
            :divisions="divisions"
            :homeCategories="homeCategories"
            :rajcategories="rajcategories"
            :techCategories="techCategories"
            :subCategories="subCategories"
            :sompadokiyos="sompadokiyos"
            :mainCategories="mainCategories"
            :subCategory="subCategory"
            :saradesh="sarades"
            :allNews="allNews"
            :bivag="bivag"
            :video="video"
        ></Home>
    </Layout>
</template>
