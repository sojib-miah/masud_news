<script setup>
import Layout from "@/Components/FrontEnd/Pathock/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import FileInput from "@/Shared/FileInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import FlashMessage from "@/Shared/FlashMessage.vue";

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
</script>

<template>
    <Head title="Pathack Profile Update" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
    <Layout>
        <form
            @submit.prevent="
                form.post(route('pathock.profile.update'), { forceFormData: true })
            "
            class="flex border rounded items-center shadow w-[50%] m-auto p-3"
        >
            <div class="w-full">
    <p class="text-2xl font-bold mb-0">Update Profile</p>
                <div class="mb-4">
                    <TextInput
                        id="name"
                        label="Name"
                        v-model="form.name"
                        :error="form.errors.name"
                    />
                </div>
                <div class="mb-4">
                    <TextInput
                        id="email"
                        label="Email"
                        v-model="form.email"
                        :error="form.errors.email"
                    />
                </div>
                <div class="mb-4">
                    <TextInput
                        id="phone"
                        label="Phone"
                        v-model="form.phone"
                        :error="form.errors.phone"
                    />
                </div>
                <div class="mb-4">
                    <TextInput
                        id="old_password"
                        label="Old Password"
                        type="password"
                        v-model="form.old_password"
                        :error="form.errors.old_password"
                    />
                </div>
                <div class="mb-4">
                    <TextInput
                        id="password"
                        label="New Password"
                        type="password"
                        v-model="form.password"
                        :error="form.errors.password"
                    />
                </div>
                <div class="mb-4">
                    <TextInput
                        id="password_confirmation"
                        label="Confirm Password"
                        type="password"
                        v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation"
                    />
                </div>
                <div class="mb-4">
                    <FileInput
                        v-model="form.image"
                        label="Profile Picture"
                        accept="image/*"
                        :error="form.errors.image"
                    />
                </div>

                <button type="submit" class="btn w-full mt-3">Update</button>
            </div>
        </form>
    </Layout>
</template>
