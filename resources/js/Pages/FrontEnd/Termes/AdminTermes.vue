<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { ref } from "vue";

const editing = ref(false);

const props = defineProps({
    term: Object,
});

const form = useForm({
    termes: props.term?.termes || "",
    privacy: props.term?.privacy || "",
});

function submit() {
    form.post(route("terms.storeOrUpdate"));
}
</script>

<template>
    <Layout>
        <Head title="Terms & Condition" />
        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />
        <Breadcrumb />

        <form
            @submit.prevent="submit"
            class="border shadow rounded p-2 border-slate-300"
        >
            <div class="flex gap-2">
                <div class="mb-3 w-1/2">
                    <label class="form-label">
                        <i class="fa-regular fa-hand-point-right"></i> Terms &
                        Conditions
                    </label>
                    <RichTextEditor
                        v-model="form.termes"
                        placeholder="Terms & Condition"
                        :error="form.errors.termes"
                    />
                </div>

                <div class="mb-3 w-1/2">
                    <label class="form-label">
                        <i class="fa-regular fa-hand-point-right"></i> Privacy
                        Policy
                    </label>
                    <RichTextEditor
                        v-model="form.privacy"
                        placeholder="Privacy Policy"
                        :error="form.errors.privacy"
                    />
                </div>
            </div>
            <div class="flex justify-end items-end w-full">
                <button
                    type="submit"
                    class="btn-success"
                    :class="{ 'btn-spinner': form.processing }"
                    :disabled="form.processing"
                >
                    <span>
                        {{
                            form.processing
                                ? "Processing..."
                                : editing
                                  ? "Update"
                                  : "Create"
                        }}
                    </span>
                    <span v-if="form.processing" class="spinner"></span>
                </button>
            </div>
        </form>
    </Layout>
</template>

<style scoped>
.btn-success {
    width: 20% !important;
}
</style>
