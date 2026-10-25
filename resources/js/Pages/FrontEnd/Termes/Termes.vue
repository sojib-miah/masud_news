<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { useForm } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { computed } from "vue";

const props = defineProps({
    term: Object,
    categories: Array,
    subCategories: Array,
});

const form = useForm({
    email: "",
});

function submit() {
    form.post(route("subscribe"));
}

const groupedSubCategories = computed(() => {
    const groups = {};
    props.subCategories.forEach((sub) => {
        if (!groups[sub.category_id]) {
            groups[sub.category_id] = [];
        }
        groups[sub.category_id].push(sub);
    });
    return groups;
});
</script>

<template>
    <Layout>
        <Head title="Termes & Condition" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <div class="container">
            <div class="flex gap-2 my-6 font-robo">
                <div
                    class="w-2/3 p-5 border border-slate-300 rounded shadow h-fit"
                >
                    <div v-html="term?.termes"></div>
                </div>
                <div
                    class="w-1/3 p-5 border border-slate-300 shadow rounded h-fit font-robo"
                >
                    <p><b>বিভাগ</b></p>
                    <ul
                        class="mb-5 flex flex-col gap-3 ml-10"
                        style="list-style: disc"
                    >
                        <li v-for="category in categories" :key="category.id">
                            <Link :href="route('all.news', [category.slug])">
                                {{ category.name }} ({{ category.news_count }})
                            </Link>

                            <ul v-if="groupedSubCategories[category.id]">
                                <li
                                    class="py-1"
                                    v-for="sub in groupedSubCategories[
                                        category.id
                                    ]"
                                    :key="sub.id"
                                >
                                    <Link
                                        :href="
                                            route('all.news', [
                                                category.slug,
                                                sub.slug,
                                            ])
                                        "
                                    >
                                        {{ sub.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <p><b>নিউজলেটার</b></p>
                    <div>
                        <form @submit.prevent="submit">
                            <label class="form-label">
                                থাকার জন্য আমাদের নিউজলেটার সাবস্ক্রাইব করুন।
                            </label>
                            <input
                                type="email"
                                v-model="form.email"
                                class="form-input"
                                placeholder="তোমার ই - মেইল ঠিকানা লেখো"
                                required
                            />
                            <button
                                type="submit"
                                class="px-3 py-2 w-full mt-2 text-white rounded bg-green-600"
                                :disabled="form.processing"
                            >
                                সাবস্ক্রাইব
                            </button>

                            <div
                                v-if="form.errors.email"
                                class="text-red-600 mt-2"
                            >
                                {{ form.errors.email }}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style>
h1,
h2,
h3,
h4,
h5,
h6,
p {
    margin-bottom: 20px;
}
ul {
    list-style: disc;
}
ul li a {
    color: #337ab7;
}
</style>
