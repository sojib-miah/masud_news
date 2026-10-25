<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import TopHeader from "./FrontEnd/Header/TopHeader.vue";
import MiddleHeader from "./FrontEnd/Header/MiddleHeader.vue";
import Header from "./FrontEnd/Header/Header.vue";
import Footer from "./FrontEnd/Footer/Footer.vue";
import Marquee from "@/Components/FrontEnd/Header/Marquee.vue";

import { usePage } from "@inertiajs/vue3";

const page = usePage();
const header = page.props.header;
const headers = page.props.headers;
const footer = page.props.footer;

const showButton = ref(false);

const handleScroll = () => {
    showButton.value = window.scrollY > 200;
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth",
    });
};

onMounted(() => {
    window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
    <div>
        <TopHeader :header="header" />
        <MiddleHeader :headers="headers" />
        <div class="sticky-wrapper">
            <Header />
        </div>
        <Marquee/>

        <main>
            <slot />
        </main>

        <button v-if="showButton" class="back-to-top" @click="scrollToTop">
            <i class="fa-solid fa-angle-up"></i>
        </button>
        <Footer  :footer="footer" />
    </div>
</template>

<style scoped>
.back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #2d8a34;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
    transition:
        background 0.3s ease,
        transform 0.3s ease;
    z-index: 1000;
}

.back-to-top:hover {
    background: #256d2a;
    transform: scale(1.1);
}.sticky-wrapper {
    position: sticky;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 999;
}

</style>
