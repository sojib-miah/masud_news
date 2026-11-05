<script setup>
import Layout from "@/Components/FrontEnd/Motamot/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { ref, computed } from "vue";

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name || "",
    email: props.user.email || "",
    phone: props.user.phone || "",
    old_password: "",
    password: "",
    password_confirmation: "",
    image: null,
});

// Local preview of newly selected image
const newImagePreview = ref(null);

const handleImage = (file) => {
    form.image = file;

    if (newImagePreview.value) {
        URL.revokeObjectURL(newImagePreview.value);
    }

    newImagePreview.value = file ? URL.createObjectURL(file) : null;
};

// Current avatar (existing image or fallback initial)
const currentAvatar = computed(() => {
    if (newImagePreview.value) return newImagePreview.value;
    if (props.user?.image) return `/storage/${props.user.image}`;
    return null;
});

const userInitial = computed(() =>
    (props.user?.name || "U").charAt(0).toUpperCase(),
);

// Custom FileInput handler — replaces direct v-model
const onFileChange = (e) => {
    handleImage(e.target.files?.[0] ?? null);
};

const submit = () => {
    form.post(route("motamot.profile.update"), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Motamot Profile Update" />

    <FlashMessage
        v-if="$page.props.flash?.status"
        :message="$page.props.flash.status"
        type="success"
    />

    <Layout>
        <!-- ==================== PAGE HEADER ==================== -->
        <div class="mb-5 sm:mb-6">
            <h1
                class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-800"
            >
                Profile Settings
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Update your account information and password
            </p>
        </div>

        <div class="max-w-5xl mx-auto">
            <form
                @submit.prevent="submit"
                class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden"
            >
                <!-- ==================== HEADER BANNER ==================== -->
                <div
                    class="relative h-24 sm:h-32"
                    style="
                        background: linear-gradient(
                            135deg,
                            #14b8a6 0%,
                            #0f9f95 100%
                        );
                    "
                >
                    <div
                        class="absolute -right-8 -top-8 w-32 h-32 bg-white/10 rounded-full"
                    ></div>
                    <div
                        class="absolute -left-4 -bottom-10 w-24 h-24 bg-white/10 rounded-full"
                    ></div>
                </div>

                <!-- ==================== AVATAR ==================== -->
                <div
                    class="relative z-10 px-4 sm:px-6 lg:px-8 -mt-12 sm:-mt-16 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4"
                >
                    <div class="flex items-end gap-4">
                        <div
                            class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl ring-4 ring-white shadow-lg overflow-hidden bg-teal-100 flex items-center justify-center shrink-0"
                        >
                            <img
                                v-if="currentAvatar"
                                :src="currentAvatar"
                                class="w-full h-full object-cover"
                                alt="Avatar"
                            />
                            <span
                                v-else
                                class="text-3xl sm:text-4xl font-extrabold text-teal-600 uppercase"
                            >
                                {{ userInitial }}
                            </span>
                        </div>

                        <div class="pb-1 sm:pb-2 min-w-0">
                            <p
                                class="text-lg sm:text-xl font-bold text-slate-800 truncate"
                            >
                                {{ props.user?.name }}
                            </p>
                            <p
                                class="text-xs sm:text-sm text-slate-500 truncate"
                            >
                                {{ props.user?.email }}
                            </p>
                        </div>
                    </div>

                    <p
                        class="text-xs text-slate-400 italic pb-1 sm:pb-2 hidden sm:block"
                    >
                        <i
                            class="fa-solid fa-shield-halved text-teal-500 mr-1"
                        ></i>
                        Your data is safe
                    </p>
                </div>

                <!-- ==================== FORM BODY ==================== -->
                <div class="p-4 sm:p-6 lg:p-8 space-y-5">
                    <!-- ============ PERSONAL INFO ============ -->
                    <section>
                        <h3
                            class="text-sm font-bold text-teal-700 uppercase mb-3 pb-2 border-b border-teal-100 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-user"></i>
                            <span>Personal Information</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <TextInput
                                    id="name"
                                    label="Name"
                                    v-model="form.name"
                                    :error="form.errors.name"
                                />
                            </div>

                            <div>
                                <TextInput
                                    id="email"
                                    label="Email"
                                    type="email"
                                    v-model="form.email"
                                    :error="form.errors.email"
                                />
                            </div>

                            <div>
                                <TextInput
                                    id="phone"
                                    label="Phone"
                                    v-model="form.phone"
                                    :error="form.errors.phone"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- ============ AVATAR ============ -->
                    <section>
                        <h3
                            class="text-sm font-bold text-teal-700 uppercase mb-3 pb-2 border-b border-teal-100 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-image"></i>
                            <span>Profile Picture</span>
                        </h3>

                        <div
                            class="flex flex-col sm:flex-row items-start sm:items-center gap-4"
                        >
                            <!-- Live preview -->
                            <div
                                class="w-20 h-20 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center shrink-0"
                            >
                                <img
                                    v-if="currentAvatar"
                                    :src="currentAvatar"
                                    class="w-full h-full object-cover"
                                    alt="Preview"
                                />
                                <i
                                    v-else
                                    class="fa-regular fa-image text-slate-300 text-2xl"
                                ></i>
                            </div>

                            <div class="flex-1 w-full">
                                <FileInput
                                    :model-value="form.image"
                                    @update:model-value="handleImage"
                                    label="Upload new picture"
                                    accept="image/*"
                                    :error="form.errors.image"
                                />
                                <p class="text-xs text-slate-500 mt-1.5">
                                    <i
                                        class="fa-solid fa-info-circle text-teal-500"
                                    ></i>
                                    JPG, PNG, or WEBP — max 2MB
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- ============ PASSWORD ============ -->
                    <section>
                        <h3
                            class="text-sm font-bold text-teal-700 uppercase mb-3 pb-2 border-b border-teal-100 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-lock"></i>
                            <span>Change Password</span>
                        </h3>

                        <p class="text-xs text-slate-500 mb-3">
                            <i
                                class="fa-solid fa-info-circle text-teal-500"
                            ></i>
                            Leave blank to keep your current password
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <TextInput
                                    id="old_password"
                                    label="Old Password"
                                    type="password"
                                    v-model="form.old_password"
                                    :error="form.errors.old_password"
                                />
                            </div>

                            <div>
                                <TextInput
                                    id="password"
                                    label="New Password"
                                    type="password"
                                    v-model="form.password"
                                    :error="form.errors.password"
                                />
                            </div>

                            <div>
                                <TextInput
                                    id="password_confirmation"
                                    label="Confirm Password"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    :error="form.errors.password_confirmation"
                                />
                            </div>
                        </div>
                    </section>

                    <!-- ============ SUBMIT ============ -->
                    <div
                        class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row gap-2 sm:gap-3"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed w-full sm:w-auto"
                        >
                            <i
                                :class="
                                    form.processing
                                        ? 'fa-solid fa-spinner fa-spin'
                                        : 'fa-solid fa-check'
                                "
                            ></i>
                            <span>
                                {{
                                    form.processing
                                        ? "Updating..."
                                        : "Update Profile"
                                }}
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Layout>
</template>

<style scoped></style>
