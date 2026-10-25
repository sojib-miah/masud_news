<script setup>
import { Swiper, SwiperSlide } from "swiper/vue";
import { Autoplay } from "swiper/modules";
import { computed } from "vue";
import "swiper/css";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    techCategories: Array,
});

const technology = computed(() =>
    props.techCategories.find((c) => c.slug === "przukti"),
);

const entertainment = computed(() =>
    props.techCategories.find((c) => c.slug === "binodn"),
);
</script>

<template>
    <div class="py-4">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Technology Section -->
                <div class="lg:col-span-2" v-if="technology">
                    <!-- Title -->
                    <div class="bg-green-500 border-l-4 border-purple-500 mb-3">
                        <h2
                            class="px-2 py-1 font-robo text-xl text-white font-semibold"
                        >
                            {{ technology.name }}
                        </h2>
                    </div>

                    <!-- Featured News -->
                    <div
                        v-if="technology.news?.length"
                        class="flex flex-col lg:flex-row gap-3 mb-4"
                    >
                        <div class="lg:w-2/5">
                            <h2
                                class="font-robo text-lg font-medium text-justify text-yellow-600"
                            >
                                {{ technology.news[0].title }}
                            </h2>
                            <p
                                class="leading-6 text-justify mt-2"
                                v-html="
                                    technology.news[0].description?.slice(
                                        0,
                                        150,
                                    )
                                "
                            ></p>
                            <Link
                                :href="`/news/details/${technology.news[0].slug}`"
                                class="text-blue-400 mt-1 inline-block"
                            >
                                ...আরও পড়ুন
                            </Link>
                        </div>
                        <div
                            class="lg:w-3/5 h-[200px] sm:h-[250px] lg:h-[300px]"
                        >
                            <img
                                :src="
                                    technology.news[0].image
                                        ? `/storage/${technology.news[0].image}`
                                        : '/storage/images/default.jpg'
                                "
                                class="w-full h-full object-cover rounded"
                                alt="news"
                            />
                        </div>
                    </div>

                    <!-- Swiper Carousel for Other News -->
                    <div class="bg-slate-200 p-2 rounded">
                        <Swiper
                            :modules="[Autoplay]"
                            :slides-per-view="1"
                            :breakpoints="{
                                640: { slidesPerView: 2, spaceBetween: 10 },
                                768: { slidesPerView: 3, spaceBetween: 10 },
                                1024: { slidesPerView: 4, spaceBetween: 10 },
                            }"
                            :autoplay="{ delay: 3000 }"
                            loop
                        >
                            <SwiperSlide
                                v-for="item in technology.news?.slice(1)"
                                :key="item.id"
                            >
                                <div class="text-center p-2">
                                    <img
                                        class="w-full h-32 sm:h-40 object-cover mb-2 rounded"
                                        :src="`/storage/${item.image}`"
                                        alt=""
                                    />
                                    <Link :href="`/news/details/${item.slug}`">
                                        <p
                                            class="text-blue-400 text-sm sm:text-base leading-5"
                                        >
                                            {{ item.title.slice(0, 40) }}...
                                        </p>
                                    </Link>
                                </div>
                            </SwiperSlide>
                        </Swiper>
                    </div>
                </div>

                <!-- Entertainment Section -->
                <div v-if="entertainment">
                    <!-- Title -->
                    <div class="bg-green-500 border-l-4 border-purple-500 mb-3">
                        <h2
                            class="px-2 py-1 font-robo text-xl text-white font-semibold"
                        >
                            {{ entertainment.name }}
                        </h2>
                    </div>

                    <!-- Featured News -->
                    <div v-if="entertainment.news?.length" class="mb-4">
                        <img
                            :src="
                                entertainment.news[0].image
                                    ? `/storage/${entertainment.news[0].image}`
                                    : '/storage/images/default.jpg'
                            "
                            class="w-full h-48 sm:h-56 lg:h-64 object-cover rounded mb-2"
                            alt="news"
                        />
                        <Link
                            :href="`/news/details/${entertainment.news[0].slug}`"
                        >
                            <p
                                class="leading-5 font-robo text-md font-semibold text-justify hover:text-blue-500"
                            >
                                {{
                                    entertainment.news[0].title
                                        .split(" ")
                                        .slice(0, 10)
                                        .join(" ")
                                }}{{
                                    entertainment.news[0].title.split(" ")
                                        .length > 10
                                        ? "..."
                                        : ""
                                }}
                            </p>
                        </Link>

                        <div
                            v-for="(item, index) in entertainment.news.slice(1)"
                            :key="item.id"
                            class="flex gap-2 border-b border-dotted py-2"
                        >
                            <div class="w-1/3 sm:w-1/4">
                                <img
                                    :src="
                                        item.image
                                            ? `/storage/${item.image}`
                                            : '/storage/images/default.jpg'
                                    "
                                    class="w-full h-20 object-cover rounded"
                                    alt="news"
                                />
                            </div>
                            <div class="flex-1">
                                <Link :href="`/news/details/${item.slug}`">
                                    <p
                                        class="leading-5 font-robo text-sm sm:text-base text-justify hover:text-blue-500"
                                    >
                                        {{
                                            item.title
                                                .split(" ")
                                                .slice(0, 10)
                                                .join(" ")
                                        }}{{
                                            item.title.split(" ").length > 10
                                                ? "..."
                                                : ""
                                        }}
                                    </p>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <Link
                        :href="route('all.news', 'binodn')"
                        class="bg-green-500 p-2 w-full text-white block text-center mt-4 rounded"
                    >
                        আরো সংবাদ...
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Responsive adjustments */
@media (max-width: 1024px) {
    .swiper-slide img {
        height: 150px !important;
    }
}
</style>
