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
                <Link :href="route('dashboard.reporter')">
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
                <!-- <Link
                    :href="route('id.card')"
                    class="text-slate-500 text-lg font-bold"
                >
                    <i class="fa-regular fa-id-badge"></i> Id Card</Link
                >
                <Link
                    :href="route('reporter.application')"
                    class="text-slate-500 text-lg font-bold"
                >
                    <i class="fa-solid fa-address-book"></i> Application
                    Form</Link
                > -->
                <Link
                    :href="route('reporter.edit', user.id)"
                    class="text-slate-500 text-lg font-bold flex gap-1 items-center"
                >
                    <img
                        :src="
                            $page.props.auth?.reporterProfile?.photo
                                ? '/storage/' +
                                  $page.props.auth.reporterProfile.photo
                                : '/storage/images/user.png'
                        "
                        alt="user image"
                        class="h-8 w-8 rounded-full object-cover" />
                    <span class="font-bold">{{ user.name }}</span>
                    <i class="fa-solid fa-caret-down"></i
                ></Link>
                <form @submit.prevent="form.post(route('user.logout'))">
                    <button type="submit" class="btn rounded">Logout</button>
                </form>
            </div>
        </header>

        <div class="flex">
            <div class="w-60 bg-white side" v-if="showAside">
                <div class="flex gap-3 flex-col w-full my-3">
                    <Link
                        :href="route('dashboard.reporter')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('dashboard.reporter')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </Link>
                    <!-- <Link
                        :href="route('add.news')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('add.news') ? 'bg-slate-200' : '',
                        ]"
                    >
                        <i class="fa-regular fa-newspaper"></i> Add News
                    </Link> -->
                    <nav>
                        <button
                            @click="toggleMenu"
                            class="flex items-center space-x-2 text-slate-500 text-lg font-bold p-2"
                        >
                            <i class="fa-solid fa-bars"></i>
                            <span>News</span>
                            <i class="fa-solid fa-angles-right"></i>
                        </button>
                        <ul v-show="menuVisible">
                            <li>
                                <Link
                                    :href="route('news.all')"
                                    :class="[
                                        'block text-slate-500 text-md font-bold p-2',
                                        route().current('news.all')
                                            ? 'bg-slate-200'
                                            : '',
                                    ]"
                                >
                                    <i class="fa-solid fa-angles-right"></i> All
                                    News
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="route('featured.news')"
                                    :class="[
                                        'block text-slate-500 text-md font-bold p-2',
                                        route().current('featured.news')
                                            ? 'bg-slate-200'
                                            : '',
                                    ]"
                                >
                                    <i class="fa-solid fa-angles-right"></i>
                                    Featured News
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="route('breaking.news')"
                                    :class="[
                                        'block text-slate-500 text-md font-bold p-2',
                                        route().current('breaking.news')
                                            ? 'bg-slate-200'
                                            : '',
                                    ]"
                                >
                                    <i class="fa-solid fa-angles-right"></i>
                                    Breaking News
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="route('pending.news')"
                                    :class="[
                                        'block text-slate-500 text-md font-bold p-2',
                                        route().current('pending.news')
                                            ? 'bg-slate-200'
                                            : '',
                                    ]"
                                >
                                    <i class="fa-solid fa-angles-right"></i>
                                    Pending News
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    <!-- <Link
                        :href="route('schedule.news')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('schedule.news') ? 'bg-slate-200' : '',
                        ]"
                    >
                        <i class="fa-regular fa-alarm-clock"></i> Schedule News
                    </Link> -->

                    <!-- <Link
                        :href="route('draft.news')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('draft.news') ? 'bg-slate-200' : '',
                        ]"
                    >
                        <i class="fa-solid fa-file-pen"></i> Draft News
                    </Link> -->

                    <!-- <Link
                        :href="route('all.reporter')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('all.reporter') ? 'bg-slate-200' : '',
                        ]"
                    >
                        <i class="fa-regular fa-file"></i> Reporter
                    </Link> -->
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
