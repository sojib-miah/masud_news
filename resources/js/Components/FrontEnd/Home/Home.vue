<script setup>
import LazySection from "../../LazySection.vue";
import Agriculture from "./Agriculture.vue";
import Health from "./Health.vue";
import Map from "./Map.vue";
import National from "./National.vue";
import Politic from "./Politic.vue";
import Projokti from "./Projokti.vue";
import Sompadokio from "./Sompadokio.vue";
import Video from "./Video.vue";
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
