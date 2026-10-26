<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    header: {
        type: Object,
        default: () => ({}),
    },
    role: String,
});

const page = usePage();
const user = page.props.auth?.user ?? null;
const form = useForm({});
const showDropdown = ref(false);

function toggleDropdown() {
    showDropdown.value = !showDropdown.value;
}

function handleClickOutside(e) {
    if (showDropdown.value && !e.target.closest(".user-menu")) {
        showDropdown.value = false;
    }
}

// -------- Date / Time --------
const currentDateTime = ref("");
let timer = null;

function updateDateTime() {
    const now = new Date();
    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "numeric",
        minute: "numeric",
        second: "numeric",
        hour12: true,
    };
    try {
        currentDateTime.value = now.toLocaleString("bn-BD", options);
    } catch {
        currentDateTime.value = now.toLocaleString();
    }
}

// -------- Dashboard route resolver --------
const dashboardRoute = computed(() => {
    const r = user?.role;
    if (r === "reporter") return route("dashboard.reporter");
    if (r === "pathock") return route("dashboard.pathock");
    if (r === "motamot") return route("dashboard.motamot");
    return route("admin.dashboard");
});

// -------- User avatar --------
const avatarSrc = computed(() =>
    user?.image ? `/storage/${user.image}` : "/storage/images/user.png",
);

onMounted(() => {
    updateDateTime();
    timer = setInterval(updateDateTime, 1000);
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="topbar">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <div class="topbar-inner">
                <!-- ============ DATE ============ -->
                <div class="date-wrap">
                    <i class="fa-regular fa-calendar date-icon"></i>
                    <span class="date-text">{{ currentDateTime }}</span>
                </div>

                <!-- ============ RIGHT SIDE ============ -->
                <div class="right-wrap">
                    <!-- Social icons -->
                    <div class="social-group">
                        <a
                            v-if="header?.youtube"
                            :href="header.youtube"
                            target="_blank"
                            rel="noopener"
                            class="social-icon"
                            aria-label="YouTube"
                        >
                            <i class="fa-brands fa-youtube"></i>
                        </a>

                        <a
                            v-if="header?.linkdin"
                            :href="header.linkdin"
                            target="_blank"
                            rel="noopener"
                            class="social-icon"
                            aria-label="LinkedIn"
                        >
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>

                        <a
                            v-if="header?.instagram"
                            :href="header.instagram"
                            target="_blank"
                            rel="noopener"
                            class="social-icon"
                            aria-label="Instagram"
                        >
                            <i class="fa-brands fa-instagram"></i>
                        </a>

                        <a
                            v-if="header?.facebook"
                            :href="header.facebook"
                            target="_blank"
                            rel="noopener"
                            class="social-icon"
                            aria-label="Facebook"
                        >
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>

                        <a
                            v-if="header?.twitter"
                            :href="header.twitter"
                            target="_blank"
                            rel="noopener"
                            class="social-icon"
                            aria-label="Twitter / X"
                        >
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                    </div>

                    <!-- Divider -->
                    <span class="divider" aria-hidden="true"></span>

                    <!-- ============ USER / GUEST ============ -->
                    <!-- Logged in -->
                    <div v-if="user" class="relative user-menu">
                        <button
                            class="user-trigger"
                            @click="toggleDropdown"
                            :aria-expanded="showDropdown"
                            aria-haspopup="true"
                        >
                            <img
                                :src="avatarSrc"
                                alt=""
                                class="user-avatar"
                                @error="
                                    (e) =>
                                        (e.target.src =
                                            '/storage/images/user.png')
                                "
                            />
                            <span class="user-name">{{ user.name }}</span>
                            <i
                                :class="[
                                    'fa-solid fa-angle-down user-caret',
                                    showDropdown ? 'rotate-180' : '',
                                ]"
                            ></i>
                        </button>

                        <!-- Dropdown -->
                        <transition name="dropdown">
                            <div
                                v-if="showDropdown"
                                class="user-dropdown"
                                role="menu"
                            >
                                <div class="dropdown-header">
                                    <img
                                        :src="avatarSrc"
                                        alt=""
                                        class="dropdown-avatar"
                                    />
                                    <div class="dropdown-info">
                                        <p class="dropdown-name">
                                            {{ user.name }}
                                        </p>
                                        <p class="dropdown-role">
                                            {{ user.role }}
                                        </p>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <Link
                                    :href="dashboardRoute"
                                    class="dropdown-item"
                                    role="menuitem"
                                >
                                    <i
                                        class="fa-solid fa-gauge-high dropdown-ico"
                                    ></i>
                                    ড্যাশবোর্ড
                                </Link>

                                <form
                                    @submit.prevent="
                                        form.post(route('user.logout'))
                                    "
                                >
                                    <button
                                        type="submit"
                                        class="dropdown-item danger"
                                        role="menuitem"
                                    >
                                        <i
                                            class="fa-solid fa-right-from-bracket dropdown-ico"
                                        ></i>
                                        লগ আউট
                                    </button>
                                </form>
                            </div>
                        </transition>
                    </div>

                    <!-- Guest -->
                    <div v-else class="guest-group">
                        <Link
                            :href="route('user.login')"
                            class="guest-btn login"
                        >
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>প্রবেশ</span>
                        </Link>

                        <Link
                            :href="route('register')"
                            class="guest-btn register"
                        >
                            <i class="fa-solid fa-user-plus"></i>
                            <span>রেজিস্ট্রেশন</span>
                        </Link>
                    </div>

                    <!-- Language selector -->
                    <select class="lang-select" aria-label="Select language">
                        <option value="bn">বাংলা</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ==================== OUTER BAR ==================== */
.topbar {
    background: linear-gradient(90deg, #00a815, #00930c, #00a815);
    background-size: 200% 200%;
    color: #fff;
    padding: 8px 0;
    font-family: "Roboto", "Hind Siliguri", sans-serif;
    font-weight: 500;
    font-size: 13.5px;
    box-shadow: 0 2px 10px rgba(0, 100, 0, 0.2);
    position: relative;
    z-index: 30;
    animation: gradientMove 8s ease infinite;
}

@keyframes gradientMove {
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

/* ==================== INNER LAYOUT ==================== */
.topbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

/* ==================== DATE ==================== */
.date-wrap {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 12px;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(4px);
    font-size: 12.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.date-icon {
    color: #d9ffe0;
    font-size: 13px;
    flex-shrink: 0;
}

.date-text {
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ==================== RIGHT SIDE ==================== */
.right-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}

/* ==================== SOCIAL ==================== */
.social-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.social-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1.5px solid rgba(255, 255, 255, 0.55);
    color: #fff;
    font-size: 12px;
    transition:
        background 0.25s,
        border-color 0.25s,
        transform 0.2s,
        box-shadow 0.25s;
}

.social-icon:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.social-icon:active {
    transform: translateY(0);
}

/* ==================== DIVIDER ==================== */
.divider {
    display: inline-block;
    width: 1px;
    height: 20px;
    background: rgba(255, 255, 255, 0.35);
}

/* ==================== USER TRIGGER ==================== */
.user-menu {
    position: relative;
}

.user-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 10px 4px 4px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    border: 1.5px solid rgba(255, 255, 255, 0.35);
    color: #fff;
    cursor: pointer;
    transition:
        background 0.2s,
        border-color 0.2s;
    font-family: inherit;
    font-size: inherit;
}

.user-trigger:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: rgba(255, 255, 255, 0.7);
}

.user-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid #fff;
    flex-shrink: 0;
}

.user-name {
    font-weight: 600;
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-caret {
    font-size: 10px;
    transition: transform 0.25s;
}

.rotate-180 {
    transform: rotate(180deg);
}

/* ==================== DROPDOWN ==================== */
.user-dropdown {
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    min-width: 220px;
    background: #ffffff;
    color: #1e293b;
    border-radius: 10px;
    box-shadow:
        0 4px 12px rgba(0, 0, 0, 0.08),
        0 12px 32px rgba(0, 0, 0, 0.12);
    overflow: hidden;
    z-index: 100;
    border: 1px solid #e2e8f0;
}

.dropdown-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px 14px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.dropdown-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.dropdown-info {
    min-width: 0;
}

.dropdown-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: #0f172a;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.2;
}

.dropdown-role {
    font-size: 0.72rem;
    color: #64748b;
    text-transform: capitalize;
    margin-top: 2px;
}

.dropdown-divider {
    height: 1px;
    background: #e2e8f0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 14px;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    transition:
        background 0.15s,
        color 0.15s;
    font-family: inherit;
}

.dropdown-item:hover {
    background: #f0fdf4;
    color: #00930c;
}

.dropdown-item.danger:hover {
    background: #fef2f2;
    color: #dc2626;
}

.dropdown-ico {
    width: 16px;
    text-align: center;
    color: #64748b;
    transition: color 0.15s;
}

.dropdown-item:hover .dropdown-ico {
    color: inherit;
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
    transition:
        opacity 0.18s ease,
        transform 0.18s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* ==================== GUEST BUTTONS ==================== */
.guest-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.guest-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 12.5px;
    text-decoration: none;
    transition:
        background 0.2s,
        transform 0.15s,
        box-shadow 0.2s;
    border: 1.5px solid transparent;
    white-space: nowrap;
}

.guest-btn.login {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.4);
}

.guest-btn.login:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: #fff;
}

.guest-btn.register {
    color: #006b09;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}

.guest-btn.register:hover {
    background: #f0fdf4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
}

.guest-btn i {
    font-size: 11px;
}

/* ==================== LANGUAGE SELECT ==================== */
.lang-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    outline: none;
    transition:
        background 0.2s,
        border-color 0.2s;
    font-family: inherit;
}

.lang-select:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: #fff;
}

.lang-select option {
    color: #1e293b;
    background: #fff;
    padding: 6px;
}

/* ==================== RESPONSIVE ==================== */
/* Tablet: hide user name in trigger, tighten spacing */
@media (max-width: 900px) {
    .user-name {
        max-width: 80px;
    }
}

/* Mobile: stack date above, hide name on trigger */
@media (max-width: 640px) {
    .topbar {
        font-size: 12.5px;
        padding: 6px 0;
    }

    .topbar-inner {
        justify-content: center;
    }

    .date-wrap {
        order: -1;
        width: 100%;
        justify-content: center;
        padding: 5px 10px;
        font-size: 11.5px;
    }

    .right-wrap {
        gap: 8px;
    }

    .user-name {
        display: none;
    }

    .divider {
        display: none;
    }

    .social-icon {
        width: 27px;
        height: 27px;
        font-size: 11px;
    }

    .guest-btn span {
        display: none;
    }

    .guest-btn {
        padding: 6px 10px;
    }

    .guest-btn i {
        font-size: 13px;
    }

    .lang-select {
        font-size: 11.5px;
        padding: 5px 8px;
    }

    .user-dropdown {
        right: auto;
        left: 50%;
        transform: translateX(-50%);
        min-width: 200px;
    }

    .dropdown-enter-from,
    .dropdown-leave-to {
        transform: translate(-50%, -6px);
    }
}

/* Very small phones */
@media (max-width: 400px) {
    .social-icon {
        width: 24px;
        height: 24px;
        font-size: 10px;
    }

    .date-wrap {
        font-size: 10.5px;
        padding: 4px 8px;
    }

    .lang-select {
        display: none;
    }
}

/* Large screens: bigger, roomier */
@media (min-width: 1280px) {
    .topbar {
        font-size: 14.5px;
        padding: 10px 0;
    }

    .social-icon {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }

    .date-wrap {
        font-size: 13.5px;
        padding: 7px 14px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .topbar {
        animation: none;
        background: #00930c;
    }
    .social-icon,
    .guest-btn,
    .user-trigger,
    .user-caret {
        transition: none;
    }
    .social-icon:hover,
    .guest-btn.register:hover {
        transform: none;
    }
}
</style>
