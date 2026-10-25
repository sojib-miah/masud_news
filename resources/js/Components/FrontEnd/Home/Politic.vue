<script setup>
import { usePage, Link } from "@inertiajs/vue3";
import { route } from "ziggy-js";
defineProps({
    rajcategories: Array,
});
const page = usePage();
const rajcategories = page.props.rajcategories ?? [];

function stripHtml(html) {
    return html.replace(/<[^>]*>/g, "");
}
function truncateText(html, wordLimit = 50) {
    const text = stripHtml(html);
    const words = text.split(/\s+/);
    return words.length > wordLimit
        ? words.slice(0, wordLimit).join(" ") + " ..."
        : text;
}
</script>

<template>
    <div class="politic bg-slate-200 py-6">
        <div class="container mx-auto px-3">
            <!-- Responsive Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="category in rajcategories"
                    :key="category.id"
                    class="w-full"
                >
                    <!-- Title -->
                    <div class="cat_title pb-3 text-center sm:text-left">
                        <Link
                            :href="route('all.news', category.slug)"
                            class="inline-block border px-3 py-2 text-sm sm:text-base"
                        >
                            {{ category.name }}
                        </Link>
                    </div>

                    <!-- Card -->
                    <div class="p-3 shadow-lg bg-white rounded">
                        <!-- Featured Image -->
                        <div v-if="category.news.length" class="mb-3">
                            <img
                                :src="
                                    category.news[0].image
                                        ? `/storage/${category.news[0].image}`
                                        : '/storage/images/default.jpg'
                                "
                                class="w-full h-[180px] sm:h-[220px] lg:h-[250px] object-cover rounded"
                                alt="news"
                            />
                        </div>

                        <!-- Featured Content -->
                        <div v-if="category.news.length">
                            <div class="border-b border-dotted pb-3 mb-3">
                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
                                        )
                                    "
                                    class="text-blue-400 hover:text-blue-500 text-sm sm:text-base font-bold font-robo leading-5"
                                >
                                    <p class="text-justify">
                                        {{
                                            category.news[0].title
                                                .split(" ")
                                                .slice(0, 10)
                                                .join(" ")
                                        }}
                                        {{
                                            category.news[0].title.split(" ")
                                                .length > 10
                                                ? "..."
                                                : ""
                                        }}
                                    </p>
                                </Link>

                                <p
                                    class="text-justify leading-5 text-xs sm:text-sm font-robo mt-1"
                                    v-html="
                                        truncateText(
                                            category.news[0]?.description,
                                            100,
                                        )
                                    "
                                ></p>

                                <Link
                                    :href="
                                        route(
                                            'news.details',
                                            category.news[0].slug,
                                        )
                                    "
                                    class="text-blue-400 text-sm"
                                >
                                    ...আরও পড়ুন
                                </Link>
                            </div>
                        </div>

                        <!-- List News -->
                        <div
                            v-for="news in category.news.slice(1)"
                            :key="news.id"
                            class="flex gap-2 border-b border-dotted py-2"
                        >
                            <img
                                :src="
                                    news.image
                                        ? `/storage/${news.image}`
                                        : '/storage/images/default.jpg'
                                "
                                class="w-[80px] sm:w-[90px] h-[60px] object-cover rounded"
                                alt="news"
                            />

                            <div>
                                <Link
                                    :href="route('news.details', news.slug)"
                                    class="text-xs sm:text-sm font-medium leading-5 hover:text-blue-500 text-justify"
                                >
                                    {{
                                        news.title
                                            .split(" ")
                                            .slice(0, 8)
                                            .join(" ")
                                    }}
                                    {{
                                        news.title.split(" ").length > 10
                                            ? "..."
                                            : ""
                                    }}
                                </Link>
                            </div>
                        </div>

                        <!-- More -->
                        <div class="flex justify-end mt-3">
                            <Link
                                :href="route('all.news', category.slug)"
                                class="text-blue-400 text-sm"
                            >
                                আরো সংবাদ...
                                <i class="fa-solid fa-angles-right"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cat_title a {
    color: #000;
    font-weight: 500;
    position: relative;
    border: 1px solid #01284f;
}

/* Arrow design (kept responsive safe) */
.cat_title a:after {
    content: "";
    position: absolute;
    top: -1px;
    left: 100%;
    border-bottom: 36px solid #000;
    border-right: 28px solid transparent;
}

/* Mobile fix (arrow size small) */
@media (max-width: 640px) {
    .cat_title a:after {
        border-bottom: 28px solid #000;
        border-right: 20px solid transparent;
    }
}
</style>
