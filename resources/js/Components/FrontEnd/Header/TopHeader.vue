<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, defineProps, onBeforeUnmount } from "vue";
import { route } from "ziggy-js";

const props = defineProps({
    header: Object,
    role: String,
});

const page = usePage();
const user = page.props.auth.user;
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
onMounted(() => document.addEventListener("click", handleClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener("click", handleClickOutside),
);

const currentDateTime = ref("");

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

    currentDateTime.value = now.toLocaleString("bn-BD", options);
}

let timer;
onMounted(() => {
    updateDateTime();
    timer = setInterval(updateDateTime, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <div class="header_section">
        <div
            class="container mx-auto flex flex-col lg:flex-row justify-between items-center gap-2"
        >
            <!-- Date -->
            <div class="text-center lg:text-left w-full lg:w-auto">
                <i class="fa-regular fa-calendar"></i>
                {{ currentDateTime }}
            </div>

            <!-- Right Section -->
            <div
                class="flex flex-wrap justify-center lg:justify-end gap-2 items-center text-white w-full lg:w-auto"
            >
                <!-- Social Icons -->
                <a v-if="header.youtube" :href="header.youtube" target="_blank">
                    <div class="icon">
                        <i class="fa-brands fa-youtube"></i>
                    </div>
                </a>

                <a v-if="header.linkdin" :href="header.linkdin" target="_blank">
                    <div class="icon">
                        <i class="fa-brands fa-linkedin-in"></i>
                    </div>
                </a>

                <a
                    v-if="header.instagram"
                    :href="header.instagram"
                    target="_blank"
                >
                    <div class="icon">
                        <i class="fa-brands fa-instagram"></i>
                    </div>
                </a>

                <a
                    v-if="header.facebook"
                    :href="header.facebook"
                    target="_blank"
                >
                    <div class="icon">
                        <i class="fa-brands fa-facebook-f"></i>
                    </div>
                </a>

                <a v-if="header.twitter" :href="header.twitter" target="_blank">
                    <div class="icon">
                        <i class="fa-brands fa-x-twitter"></i>
                    </div>
                </a>

                <!-- User Menu -->
                <div v-if="user" class="relative user-menu">
                    <div
                        class="flex items-center gap-2 text-white cursor-pointer"
                        @click="toggleDropdown"
                    >
                        <img
                            :src="
                                user.image
                                    ? '/storage/' + user.image
                                    : '/storage/images/user.png'
                            "
                            alt="user image"
                            class="h-8 w-8 rounded-full object-cover"
                        />

                        <!-- Hide name on small screen -->
                        <span class="font-bold hidden sm:block">
                            {{ user.name }}
                        </span>

                        <i class="fa-solid fa-angle-down"></i>
                    </div>

                    <!-- Dropdown -->
                    <div
                        v-if="showDropdown"
                        class="absolute right-0 mt-2 w-40 sm:w-48 bg-white text-black rounded shadow-lg p-2 z-50"
                    >
                        <Link
                            :href="
                                user.role === 'reporter'
                                    ? route('dashboard.reporter')
                                    : user.role === 'pathock'
                                      ? route('dashboard.pathock')
                                      : user.role === 'motamot'
                                        ? route('dashboard.motamot')
                                        : route('admin.dashboard')
                            "
                            class="block px-2 py-1 text-black mb-2 hover:bg-gray-100 font-bold"
                        >
                            Dashboard
                        </Link>

                        <form @submit.prevent="form.post(route('user.logout'))">
                            <button
                                type="submit"
                                class="block w-full text-left px-2 py-1 hover:bg-gray-100 font-bold"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Guest -->
                <div
                    v-else
                    class="flex gap-2 text-sm sm:text-base items-center"
                >
                    <Link :href="route('user.login')">
                        <i class="fa-solid fa-right-to-bracket"></i> প্রবেশ করুন
                    </Link>

                    <Link :href="route('register')">
                        <i class="fa-solid fa-user"></i> রেজিস্ট্রেশন
                    </Link>
                </div>

                <!-- Language -->
                <select class="ml-2 sm:ml-4 text-sm sm:text-base">
                    <option value="">Bangla</option>
                    <option value="">English</option>
                </select>
            </div>
        </div>
    </div>
</template>

<style scoped>
.header_section {
    background: linear-gradient(90deg, #8aff90, #00930c);
    background-size: 200% 200%;
    padding: 6px 0;
    width: 100%;
    color: #fff;
    font-family: "Roboto", sans-serif;
    font-weight: 500;
    font-size: 14px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    animation: gradientMove 6s ease infinite;
}

/* Desktop font একটু বড় */
@media (min-width: 640px) {
    .header_section {
        font-size: 16px;
    }
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

.icon {
    border: 2px solid rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

/* Desktop এ একটু বড় icon */
@media (min-width: 1024px) {
    .icon {
        padding: 8px;
    }
}

.icon:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

select {
    padding: 4px 8px;
    border-radius: 5px;
    border: none;
    outline: none;
    background: #fff;
    color: #333;
    font-size: 13px;
    cursor: pointer;
}

/* Desktop select */
@media (min-width: 640px) {
    select {
        font-size: 14px;
        padding: 5px 10px;
    }
}

select:hover {
    background: #f1f1f1;
}
</style>
