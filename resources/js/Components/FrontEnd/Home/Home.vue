<script setup>
import { defineAsyncComponent } from "vue";
import LazySection from "../../LazySection.vue";

const Agriculture = defineAsyncComponent(() => import("./Agriculture.vue"));
const Health = defineAsyncComponent(() => import("./Health.vue"));
const Map = defineAsyncComponent(() => import("./Map.vue"));
const National = defineAsyncComponent(() => import("./National.vue"));
const Politic = defineAsyncComponent(() => import("./Politic.vue"));
const Projokti = defineAsyncComponent(() => import("./Projokti.vue"));
const Sompadokio = defineAsyncComponent(() => import("./Sompadokio.vue"));
const Video = defineAsyncComponent(() => import("./Video.vue"));

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
    <div>
        <LazySection section="video">
            <Video :video="video" />
        </LazySection>

        <LazySection section="national">
            <National :homeCategories="homeCategories" />
        </LazySection>

        <LazySection section="politic">
            <Politic :rajcategories="rajcategories" />
        </LazySection>

        <LazySection section="projokti">
            <Projokti :techCategories="techCategories" />
        </LazySection>

        <LazySection section="health">
            <Health :subCategories="subCategories" />
        </LazySection>

        <LazySection section="sompadokio">
            <Sompadokio :sompadokiyos="sompadokiyos" />
        </LazySection>

        <LazySection section="agriculture">
            <Agriculture
                :mainCategories="mainCategories"
                :subCategory="subCategory"
            />
        </LazySection>

        <LazySection section="map">
            <Map
                :divisions="divisions"
                :saradesh="sarades"
                :allNews="allNews"
                :bivag="bivag"
            />
        </LazySection>
    </div>
</template>
