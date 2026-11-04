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

// Back to Top
const showButton = ref(false);

// Bottom Alert
const showAlert = ref(true);

const handleScroll = () => {
    showButton.value = window.scrollY > 200;
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth",
    });
};

const closeAlert = () => {
    showAlert.value = false;
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
        <!-- Header -->
        <TopHeader :header="header" />
        <MiddleHeader :headers="headers" />

        <div class="sticky-wrapper">
            <Header />
        </div>

        <Marquee />

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Bottom Left Alert -->
        <Transition name="alert-slide">
            <div v-if="showAlert" class="news-alert">
                <div class="alert-icon">
                    <i class="fa-solid fa-newspaper"></i>
                </div>

                <div class="alert-content">
                    <h4>খবর পড়ুন</h4>
                    <p>মাসে ২০ হাজারের অধিক আয় করুন</p>
                </div>

                <button
                    type="button"
                    class="alert-close"
                    @click="closeAlert"
                    aria-label="Close alert"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </Transition>

        <!-- Back To Top Button -->
        <Transition name="top-button">
            <button
                v-if="showButton"
                type="button"
                class="back-to-top"
                @click="scrollToTop"
                aria-label="Back to top"
            >
                <i class="fa-solid fa-angle-up"></i>
            </button>
        </Transition>

        <!-- Footer -->
        <Footer :footer="footer" />
    </div>
</template>

<style scoped>
/* =========================
   STICKY HEADER
========================= */
.sticky-wrapper {
    position: sticky;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 999;
}

/* =========================
   BACK TO TOP BUTTON
========================= */
.back-to-top {
    position: fixed;
    bottom: 25px;
    right: 25px;

    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;

    border: none;
    outline: none;
    border-radius: 50%;

    font-size: 20px;
    cursor: pointer;

    box-shadow: 0 5px 18px rgba(22, 163, 74, 0.35);

    transition: all 0.3s ease;
    z-index: 9999;
}

.back-to-top:hover {
    background: #166534;
    transform: translateY(-5px);
    box-shadow: 0 8px 22px rgba(22, 163, 74, 0.45);
}

.back-to-top:active {
    transform: scale(0.9);
}

/* =========================
   BOTTOM LEFT NEWS ALERT
========================= */
.news-alert {
    position: fixed;
    bottom: 22px;
    left: 22px;

    display: flex;
    align-items: center;
    gap: 12px;

    max-width: calc(100vw - 100px);
    padding: 12px 16px;

    background: #ffffff;
    border: 1px solid #dcfce7;
    border-left: 4px solid #16a34a;

    border-radius: 14px;

    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);

    z-index: 9998;

    animation: alertFloat 3s ease-in-out infinite;
}

/* Alert Icon */
.alert-icon {
    width: 42px;
    height: 42px;
    min-width: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #dcfce7;
    color: #15803d;

    border-radius: 50%;
    font-size: 19px;
}

/* Alert Text */
.alert-content {
    flex: 1;
}

.alert-content h4 {
    margin: 0 0 4px;
    color: #166534;
    font-size: 15px;
    font-weight: 800;
}

.alert-content p {
    margin: 0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
}

/* Close Button */
.alert-close {
    width: 25px;
    height: 25px;
    min-width: 25px;

    display: flex;
    align-items: center;
    justify-content: center;

    border: none;
    border-radius: 50%;

    background: #f1f5f9;
    color: #64748b;

    cursor: pointer;
    transition: all 0.2s ease;
}

.alert-close:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* =========================
   ALERT FLOAT ANIMATION
========================= */
@keyframes alertFloat {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-4px);
    }
}

/* =========================
   TRANSITIONS
========================= */
.alert-slide-enter-active,
.alert-slide-leave-active {
    transition: all 0.4s ease;
}

.alert-slide-enter-from,
.alert-slide-leave-to {
    opacity: 0;
    transform: translateX(-40px);
}

.top-button-enter-active,
.top-button-leave-active {
    transition: all 0.3s ease;
}

.top-button-enter-from,
.top-button-leave-to {
    opacity: 0;
    transform: translateY(15px);
}

/* =========================
   MOBILE RESPONSIVE
========================= */
@media (max-width: 480px) {
    .news-alert {
        left: 10px;
        bottom: 12px;
        padding: 9px 10px;
        gap: 8px;
        border-radius: 11px;
        max-width: calc(100vw - 75px);
    }

    .alert-icon {
        width: 34px;
        height: 34px;
        min-width: 34px;
        font-size: 15px;
    }

    .alert-content h4 {
        font-size: 13px;
    }

    .alert-content p {
        font-size: 10px;
    }

    .back-to-top {
        width: 42px;
        height: 42px;
        right: 12px;
        bottom: 14px;
        font-size: 17px;
    }

    .alert-close {
        width: 21px;
        height: 21px;
        min-width: 21px;
        font-size: 11px;
    }
}
</style>
