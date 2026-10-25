<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { ref } from "vue";

const showAside = ref(true);
const form = useForm({});
</script>

<template>
    <div>
        <header class="bg-white shadow p-2 flex justify-between">
            <div class="flex gap-5 items-center">
                <Link :href="route('dashboard.pathock')">
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
                    :href="route('pathock.profile')"
                    class="text-slate-500 text-lg font-bold"
                >
                    <i class="fa-regular fa-user"></i> Profile</Link
                >

                <div class="relative">
                    <Link :href="route('cart.index')">
                        <i class="fa-solid fa-cart-plus text-2xl"></i>
                    </Link>
                    <span
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                    >
                        {{ $page.props.cartCount }}
                    </span>
                </div>

                <form @submit.prevent="form.post(route('user.logout'))">
                    <button type="submit" class="btn rounded">Logout</button>
                </form>
            </div>
        </header>

        <div class="flex">
            <div class="w-60 bg-white side" v-if="showAside">
                <div class="flex gap-3 flex-col w-full my-3">
                    <Link
                        :href="route('dashboard.pathock')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('dashboard.pathock')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </Link>

                    <!-- <Link
                        :href="route('pathock.payment')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('pathock.payment')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-file-invoice-dollar"></i> Payment
                        Request
                    </Link> -->

                    <Link
                        :href="route('pathock.refferals')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('pathock.refferals')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-bacteria"></i> Pathock Refferals
                    </Link>

                    <Link
                        :href="route('stock-money.index')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('stock-money.index')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-solid fa-arrow-trend-up"></i> Stock Money
                    </Link>

                    <Link
                        :href="route('pathock.orders')"
                        :class="[
                            'text-slate-500 text-lg font-bold p-2',
                            route().current('pathock.orders')
                                ? 'bg-slate-200'
                                : '',
                        ]"
                    >
                        <i class="fa-brands fa-jedi-order"></i> My Orders
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
</style>
