<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";
import FlashMessage from "@/Shared/FlashMessage.vue";

const props = defineProps({
    footer: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const currentYear = new Date().getFullYear();

const form = useForm({
    email: "",
});

function submit() {
    form.post(route("subscribe"), {
        preserveScroll: true,
        onSuccess: () => form.reset("email"),
    });
}

// Safe contact info getters
const phone1 = computed(() => props.footer?.phone1 || "");
const phone2 = computed(() => props.footer?.phone2 || "");
const emailAddr = computed(() => props.footer?.email || "");
const web1 = computed(() => props.footer?.web1 || "");
const web2 = computed(() => props.footer?.web2 || "");
</script>

<template>
    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <footer class="site-footer">
        <!-- Decorative top accent -->
        <div class="footer-accent" aria-hidden="true"></div>

        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <!-- ============ MAIN CONTENT ============ -->
            <div class="footer-main">
                <!-- ==================== LEFT: OFFICE INFO ==================== -->
                <div class="footer-col footer-col-info">
                    <h3 class="footer-col-title">
                        <i class="fa-solid fa-newspaper"></i>
                        সম্পাদনা পর্ষদ
                    </h3>

                    <ul class="footer-list">
                        <li v-if="footer?.som">
                            <span class="lbl">সম্পাদক ও প্রকাশক : </span>
                            <span class="val">{{ footer.som }}</span>
                        </li>
                        <li v-if="footer?.bab">
                            <span class="lbl">ব্যবস্থাপনা সম্পাদক : </span>
                            <span class="val">{{ footer.bab }}</span>
                        </li>
                        <li v-if="footer?.nir">
                            <span class="lbl">নির্বাহী সম্পাদক : </span>
                            <span class="val">{{ footer.nir }}</span>
                        </li>
                    </ul>
                </div>

                <!-- ==================== MIDDLE: CONTACT ==================== -->
                <div class="footer-col footer-col-contact">
                    <h3 class="footer-col-title">
                        <i class="fa-solid fa-address-book"></i>
                        যোগাযোগ
                    </h3>

                    <ul class="footer-list contact">
                        <li v-if="footer?.ban">
                            <i class="fa-solid fa-location-dot ico"></i>
                            <span class="val">{{ footer.ban }}</span>
                        </li>

                        <li v-if="phone1 || phone2">
                            <i class="fa-solid fa-phone ico"></i>
                            <span class="val">
                                <a
                                    v-if="phone1"
                                    :href="`tel:${phone1}`"
                                    class="link"
                                >
                                    {{ phone1 }}
                                </a>
                                <template v-if="phone1 && phone2">, </template>
                                <a
                                    v-if="phone2"
                                    :href="`tel:${phone2}`"
                                    class="link"
                                >
                                    {{ phone2 }}
                                </a>
                            </span>
                        </li>

                        <li v-if="emailAddr">
                            <i class="fa-solid fa-envelope ico"></i>
                            <span class="val">
                                <a :href="`mailto:${emailAddr}`" class="link">
                                    {{ emailAddr }}
                                </a>
                            </span>
                        </li>

                        <li v-if="web1 || web2">
                            <i class="fa-solid fa-globe ico"></i>
                            <span class="val">
                                <a
                                    v-if="web1"
                                    :href="web1"
                                    target="_blank"
                                    rel="noopener"
                                    class="link"
                                >
                                    {{ web1 }}
                                </a>
                                <template v-if="web1 && web2">
                                    <span class="sep">|</span>
                                </template>
                                <a
                                    v-if="web2"
                                    :href="web2"
                                    target="_blank"
                                    rel="noopener"
                                    class="link"
                                >
                                    {{ web2 }}
                                </a>
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- ==================== RIGHT: NEWSLETTER ==================== -->
                <div class="footer-col footer-col-newsletter">
                    <h3 class="footer-col-title">
                        <i class="fa-solid fa-envelope-open-text"></i>
                        নিউজলেটার
                    </h3>

                    <p class="newsletter-desc">
                        সর্বশেষ সংবাদ সবার আগে পেতে আপনার ইমেইল দিয়ে
                        সাবস্ক্রাইব করুন।
                    </p>

                    <form class="newsletter" @submit.prevent="submit">
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope input-ico"></i>
                            <input
                                type="email"
                                v-model="form.email"
                                placeholder="আপনার ইমেইল লিখুন..."
                                required
                                class="newsletter-input"
                                :disabled="form.processing"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="newsletter-btn"
                        >
                            <i
                                v-if="form.processing"
                                class="fa-solid fa-spinner fa-spin"
                            ></i>
                            <i v-else class="fa-solid fa-paper-plane"></i>
                            {{
                                form.processing
                                    ? "অপেক্ষা করুন..."
                                    : "সদস্য হোন"
                            }}
                        </button>

                        <p v-if="form.errors.email" class="newsletter-error">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ form.errors.email }}
                        </p>
                    </form>

                    <div class="footer-links">
                        <Link :href="route('news.termes')" class="footer-link">
                            Terms & Conditions
                        </Link>
                        <Link :href="route('news.privacy')" class="footer-link">
                            Privacy Policy
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ============ BOTTOM BAR ============ -->
            <div class="footer-bottom">
                <p class="copyright">
                    কপিরাইট &copy; {{ currentYear }} সর্বস্বত্ব সংরক্ষিত
                    <template v-if="footer?.copy">
                        | {{ footer.copy }}
                    </template>
                </p>
                <p class="designer">
                    ডিজাইন ও ডেভেলপমেন্ট
                    <a
                        href="https://www.sojib.comitsbd.com"
                        target="_blank"
                        rel="noopener"
                        class="designer-link"
                    >
                        Md: Sojib Mia
                    </a>
                </p>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* ==================== FOOTER BASE ==================== */
.site-footer {
    position: relative;
    background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
    background-size: 200% 200%;
    color: #e5e7eb;
    padding: 0 0 20px;
    font-family: "Hind Siliguri", "Roboto", sans-serif;
    animation: footerGradient 14s ease infinite;
    overflow: hidden;
}

/* Optional subtle texture overlay */
.site-footer::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(
            circle at 20% 10%,
            rgba(255, 255, 255, 0.04),
            transparent 60%
        ),
        radial-gradient(
            circle at 80% 90%,
            rgba(255, 255, 255, 0.04),
            transparent 60%
        );
    pointer-events: none;
}

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

/* Top accent line */
.footer-accent {
    height: 4px;
    background: linear-gradient(90deg, #8aff90, #00930c, #8aff90);
    background-size: 200% 100%;
    animation: accentMove 8s linear infinite;
}

@keyframes accentMove {
    0% {
        background-position: 0% 50%;
    }
    100% {
        background-position: 200% 50%;
    }
}

/* ==================== MAIN LAYOUT ==================== */
.footer-main {
    position: relative;
    display: grid;
    grid-template-columns: 1fr;
    gap: 28px;
    padding: 36px 0 24px;
}

@media (min-width: 640px) {
    .footer-main {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }
    .footer-col-newsletter {
        grid-column: span 2;
    }
}

@media (min-width: 1024px) {
    .footer-main {
        grid-template-columns: 1.1fr 1fr 1.2fr;
        gap: 40px;
        padding: 44px 0 28px;
    }
    .footer-col-newsletter {
        grid-column: auto;
    }
}

/* ==================== COLUMN ==================== */
.footer-col {
    min-width: 0;
}

.footer-col-title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 14px;
    padding-bottom: 8px;
    position: relative;
    letter-spacing: 0.2px;
}

.footer-col-title i {
    color: #8aff90;
    font-size: 0.9rem;
}

/* Underline accent */
.footer-col-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 32px;
    height: 2px;
    background: #8aff90;
    border-radius: 2px;
}

@media (min-width: 640px) {
    .footer-col-title {
        font-size: 1.05rem;
    }
}

/* ==================== LIST ==================== */
.footer-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.footer-list li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 0.85rem;
    line-height: 1.6;
    color: #d1fae5;
}

/* Info variant: label + value */
.footer-list li > .lbl {
    color: #a7f3d0;
    font-weight: 600;
    min-width: 130px;
    flex-shrink: 0;
}

.footer-list li > .val {
    color: #f3f4f6;
    word-break: break-word;
}

/* Contact variant: icon + value */
.footer-list.contact li {
    gap: 10px;
}

.footer-list.contact .ico {
    color: #8aff90;
    font-size: 0.85rem;
    margin-top: 3px;
    flex-shrink: 0;
    width: 16px;
    text-align: center;
}

.footer-list.contact .val {
    color: #f3f4f6;
    word-break: break-word;
}

/* Small phones: stack label above value */
@media (max-width: 399px) {
    .footer-list li {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
    .footer-list li > .lbl {
        min-width: 0;
    }
}

/* ==================== LINK STYLES ==================== */
.link {
    color: #8aff90;
    text-decoration: none;
    transition: color 0.2s;
    word-break: break-all;
}

.link:hover {
    color: #ffffff;
    text-decoration: underline;
}

.sep {
    color: #a7f3d0;
    opacity: 0.5;
    margin: 0 2px;
}

/* ==================== NEWSLETTER ==================== */
.newsletter-desc {
    font-size: 0.83rem;
    line-height: 1.6;
    color: #d1fae5;
    margin: 0 0 14px;
}

.newsletter {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 16px;
}

.input-wrap {
    position: relative;
}

.input-ico {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 0.85rem;
    pointer-events: none;
}

.newsletter-input {
    width: 100%;
    padding: 12px 16px 12px 40px;
    border: 1.5px solid rgba(255, 255, 255, 0.25);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    border-radius: 8px;
    font-size: 0.88rem;
    transition:
        border-color 0.2s,
        background 0.2s,
        box-shadow 0.2s;
    font-family: inherit;
    outline: none;
}

.newsletter-input::placeholder {
    color: #a7f3d0;
    opacity: 0.85;
}

.newsletter-input:hover {
    background: rgba(255, 255, 255, 0.12);
}

.newsletter-input:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #8aff90;
    box-shadow: 0 0 0 3px rgba(138, 255, 144, 0.15);
}

.newsletter-input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.newsletter-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 18px;
    background: linear-gradient(135deg, #8aff90, #00930c);
    color: #063a10;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition:
        transform 0.2s,
        box-shadow 0.2s;
    box-shadow: 0 4px 14px rgba(138, 255, 144, 0.25);
    font-family: inherit;
}

.newsletter-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px rgba(138, 255, 144, 0.35);
}

.newsletter-btn:active:not(:disabled) {
    transform: translateY(0);
}

.newsletter-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.newsletter-btn i {
    font-size: 0.85rem;
}

/* Error */
.newsletter-error {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #fca5a5;
    font-size: 0.8rem;
    margin: 0;
    padding: 6px 10px;
    background: rgba(239, 68, 68, 0.12);
    border-radius: 6px;
    border: 1px solid rgba(239, 68, 68, 0.25);
}

.newsletter-error i {
    font-size: 0.75rem;
    flex-shrink: 0;
}

/* ==================== FOOTER LINKS ==================== */
.footer-links {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 16px;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
}

.footer-link {
    color: #a7f3d0;
    font-size: 0.82rem;
    text-decoration: none;
    transition: color 0.2s;
    position: relative;
}

.footer-link::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 0;
    height: 1px;
    background: #8aff90;
    transition: width 0.25s ease;
}

.footer-link:hover {
    color: #fff;
}

.footer-link:hover::after {
    width: 100%;
}

/* ==================== BOTTOM BAR ==================== */
.footer-bottom {
    position: relative;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    text-align: center;
    font-size: 0.8rem;
    color: #a7f3d0;
}

@media (min-width: 640px) {
    .footer-bottom {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        text-align: left;
    }
}

.copyright,
.designer {
    margin: 0;
    line-height: 1.6;
}

.designer-link {
    color: #8aff90;
    font-weight: 700;
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-underline-offset: 2px;
    transition: color 0.2s;
}

.designer-link:hover {
    color: #fff;
}

/* ==================== LARGE DESKTOP ==================== */
@media (min-width: 1280px) {
    .footer-col-title {
        font-size: 1.1rem;
    }
    .footer-list li {
        font-size: 0.88rem;
    }
    .newsletter-input {
        font-size: 0.92rem;
        padding: 13px 16px 13px 42px;
    }
    .newsletter-btn {
        font-size: 0.94rem;
        padding: 13px 20px;
    }
    .footer-bottom {
        font-size: 0.85rem;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .site-footer {
        animation: none;
        background: #064e3b;
    }
    .footer-accent {
        animation: none;
        background: linear-gradient(90deg, #8aff90, #00930c);
    }
    .footer-link,
    .footer-link::after,
    .link,
    .designer-link,
    .newsletter-btn,
    .newsletter-input {
        transition: none;
    }
    .newsletter-btn:hover:not(:disabled) {
        transform: none;
    }
}
</style>
