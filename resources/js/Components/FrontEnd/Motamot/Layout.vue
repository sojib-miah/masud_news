<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { computed, ref } from "vue";

const showAside = ref(true);
const form = useForm({});
const page = usePage();
const user = page.props.auth.user;

const showMenu = ref(false);

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};
const isActiveNews = computed(
    () =>
        route().current("news.all") ||
        route().current("featured.news") ||
        route().current("breaking.news") ||
        route().current("pending.news"),
);
const menuVisible = computed(() => showMenu.value || isActiveNews.value);
</script>

<template>
    <div>
        <header class="bg-white shadow p-2 flex justify-between">
            <div class="flex gap-5 items-center">
                <Link :href="route('dashboard.motamot')">
                    <img
                        :src="`/storage/${$page.props.site?.logo}`"
                        class="h-20"
                        alt=""
                    />
                </Link>
                <button
                    type="button"
                    @click="showAside = !showAside"
                    class="text-3xl font-bold"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            <div class="flex gap-4 items-center">
                <a
                    :href="route('home')"
                    target="_blank"
                    class="text-slate-500 text-lg font-bold"
                >
                    <i class="fa-regular fa-eye"></i> View Site</a
                >
                <Link
                    :href="route('motamot.profile')"
                    class="text-slate-500 text-lg font-bold flex gap-1 items-center"
                >
                    Profile</Link
                >
                <form @submit.prevent="form.post(route('user.logout'))">
                    <button type="submit" class="btn rounded">Logout</button>
                </form>
            </div>
        </header>

        <div class="flex">
            <div class="w-60 bg-white side" v-if="showAside">
                <div class="flex gap-3 flex-col w-full my-3">
                    <Link
                        :href="route('dashboard.motamot')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('dashboard.motamot')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </Link>
                    <Link
                        :href="route('motamot.index')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('motamot.index')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-manat-sign"></i> Motamot
                    </Link>
                </div>
            </div>

            <main class="bg-slate-200 p-3 w-full">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.side,
main {
    min-height: 90vh;
}
ul,
li {
    list-style: none;
}
ul {
    margin-left: 20px !important;
}
</style>
