<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { defineProps } from "vue";
import { route } from "ziggy-js";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    footer: Object,
});

const currentYear = new Date().getFullYear();

const form = useForm({
    email: "",
});

function submit() {
    form.post(route("subscribe"));
}
</script>

<template>
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <div class="footer">
        <div class="container mx-auto px-3">
            <!-- Content -->
            <div class="footer-content">
                <!-- Left Info -->
                <div class="footer-info">
                    <!-- Editor -->
                    <div class="editor-info">
                        <p class="leading-6">
                            <b>সম্পাদক & প্রকাশক : </b> {{ footer?.som || "" }}
                        </p>
                        <!-- <p class="leading-6"><b>প্রকাশক : </b> {{ footer?.pro || "" }}</p> -->
                        <p class="leading-6">
                            <b>ব্যবস্থাপনা সম্পাদক : </b>
                            {{ footer?.bab || "" }}
                        </p>
                        <p class="leading-6">
                            <b>নির্বাহী সম্পাদক : </b> {{ footer?.nir || "" }}
                        </p>
                    </div>

                    <!-- Office -->
                    <div class="office-info">
                        <p class="leading-6">
                            <b>বাণিজ্যিক কার্যালয় : </b> {{ footer?.ban || "" }}
                        </p>

                        <p class="leading-6">
                            <b>ফোন : </b>
                            <a :href="`tel:${footer?.phone1 || ''}`">
                                {{ footer?.phone1 || "" }} </a
                            >,
                            <a :href="`tel:${footer?.phone2 || ''}`">
                                {{ footer?.phone2 || "" }}
                            </a>
                        </p>

                        <p class="leading-6">
                            <b>ই-মেইল : </b>
                            <a :href="`mailto:${footer?.email || ''}`">
                                {{ footer?.email || "" }}
                            </a>
                        </p>

                        <p class="leading-6">
                            <b>ওয়েবসাইট : </b>
                            <a :href="footer?.web1 || ''" target="_blank">
                                {{ footer?.web1 || "" }}
                            </a>
                            |
                            <a :href="footer?.web2 || ''" target="_blank">
                                {{ footer?.web2 || "" }}
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Right -->
                <div class="footer-right">
                    <!-- Newsletter -->
                    <form class="newsletter" @submit.prevent="submit">
                        <input
                            type="email"
                            v-model="form.email"
                            placeholder="আপনার ইমেইল লিখুন...."
                            required
                            class="px-4 py-2 rounded-sm w-full border border-green-500 focus:outline-none focus:ring-2 focus:ring-green-600"
                        />
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="py-2 rounded-sm bg-green-600 hover:bg-green-700 text-white w-full mt-2"
                        >
                            সদস্য হোন
                        </button>
                    </form>

                    <span v-if="form.errors.email" class="error">
                        {{ form.errors.email }}
                    </span>

                    <!-- Links -->
                    <div class="footer-links">
                        <Link :href="route('news.termes')">
                            Terms & Conditions
                        </Link>
                        <Link :href="route('news.privacy')">
                            Privacy Policy
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Bottom -->
            <div class="footer-bottom">
                <p class="mt-5 leading-6">
                    কপিরাইট &copy; {{ currentYear }} সর্বস্বত্ব সংরক্ষিত |
                    {{ footer?.copy || "" }} | ডিজাইন ও ডেভেলপমেন্ট
                    <a
                        href="https://www.sojib.comitsbd.com"
                        target="_blank"
                        class="text-green-600 font-bold underline"
                    >
                        Md: Sojib Mia</a
                    >
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.footer {
    background: linear-gradient(270deg, rgb(255, 148, 148), lightgreen);
    background-size: 400% 400%;
    color: #000;
    padding: 20px 10px;
    animation: footerGradient 8s ease infinite;
}

/* Animation */
@keyframes footerGradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Layout */
.footer-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Tablet */
@media (min-width: 640px) {
    .footer-content {
        flex-direction: column;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .footer-content {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
    }
}

/* Info section */
.footer-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Desktop দুই কলাম */
@media (min-width: 640px) {
    .footer-info {
        flex-direction: row;
        gap: 30px;
    }
}

.editor-info {
    border-right: none;
}

/* Desktop border */
@media (min-width: 640px) {
    .editor-info {
        border-right: 1px solid rgba(255, 255, 255, 0.3);
        padding-right: 20px;
    }
}

.office-info p,
.editor-info p {
    margin-bottom: 10px;
    font-size: 14px;
}
.office-info a {
    color: green;
    text-decoration: underline;
}
/* Right */
.footer-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
/* Links */
.footer-links {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Desktop */
@media (min-width: 640px) {
    .footer-links {
        flex-direction: row;
        gap: 20px;
    }
}

.footer-links a {
    color: green;
    font-size: 14px;
}

/* Bottom */
.footer-bottom {
    text-align: center;
    margin-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.3);
    padding-top: 10px;
    font-size: 13px;
}

/* Error */
.error {
    color: red;
    font-size: 13px;
}
</style>
