<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Shared/Layout.vue";
import { useForm } from "@inertiajs/vue3";
import { route } from "ziggy-js";

const form = useForm({
    login_field: "",
});

const submit = () => {
    form.post(route("admin.impersonate.login"), {
        preserveScroll: true,
        onError: () => form.reset("login_field"),
    });
};
</script>

<template>
    <Head title="Impersonation Login Page" />
    <Layout>
        <div class="bg-white p-4 rounded-xl shadow border border-slate-200">
            <h3 class="font-bold text-slate-800 mb-3 text-sm">
                ইউজার হিসেবে লগইন (Impersonate)
            </h3>

            <form @submit.prevent="submit" class="flex gap-2">
                <input
                    v-model="form.login_field"
                    type="text"
                    placeholder="ইউজার আইডি অথবা ফোন নম্বর..."
                    class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-full focus:ring-2 focus:ring-indigo-500 outline-none"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition disabled:opacity-50"
                >
                    লগইন
                </button>
            </form>
            <div
                v-if="form.errors.login_field"
                class="text-red-500 text-xs mt-2"
            >
                {{ form.errors.login_field }}
            </div>
        </div>
    </Layout>
</template>
