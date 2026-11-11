<script setup>
import Layout from "@/Shared/Layout.vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { route } from "ziggy-js";
import RichTextEditor from "@/Shared/RichTextEditor.vue";

const { site } = usePage().props;

const form = useForm({
    docs: site?.docs || "",
});

// View mode for mobile/desktop toggle
const viewMode = ref("split"); // 'split' | 'preview' | 'edit'

function submit() {
    form.post(route("admin.docs.update"), {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: ["site"] });
        },
    });
}
</script>

<template>
    <Layout>
        <Head title="Documentation Settings" />

        <Breadcrumb />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <!-- ==================== PAGE HEADER ==================== -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-6 sm:px-8 shadow-xl mb-6"
        >
            <div
                class="absolute -top-16 -right-16 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"
            ></div>
            <div
                class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"
            ></div>
            <div
                class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <p
                        class="text-indigo-400 text-xs font-semibold uppercase tracking-wider mb-1"
                    >
                        <i class="fa-solid fa-book-open mr-1"></i> System
                        Documentation
                    </p>
                    <h1
                        class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight"
                    >
                        Documentation Settings
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Write and update guidelines for administrators.
                    </p>
                </div>
            </div>
        </div>

        <!-- ==================== MOBILE VIEW TOGGLE ==================== -->
        <div
            class="flex items-center justify-center p-1 bg-slate-100 rounded-lg mb-4 lg:hidden border border-slate-200"
        >
            <button
                type="button"
                @click="viewMode = 'edit'"
                class="flex-1 py-2 text-sm font-bold rounded-md transition-all"
                :class="
                    viewMode === 'edit'
                        ? 'bg-white text-slate-900 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700'
                "
            >
                <i class="fa-solid fa-pen mr-1.5"></i> Edit
            </button>
            <button
                type="button"
                @click="viewMode = 'preview'"
                class="flex-1 py-2 text-sm font-bold rounded-md transition-all"
                :class="
                    viewMode === 'preview'
                        ? 'bg-white text-slate-900 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700'
                "
            >
                <i class="fa-solid fa-eye mr-1.5"></i> Preview
            </button>
            <button
                type="button"
                @click="viewMode = 'split'"
                class="flex-1 py-2 text-sm font-bold rounded-md transition-all"
                :class="
                    viewMode === 'split'
                        ? 'bg-white text-slate-900 shadow-sm'
                        : 'text-slate-500 hover:text-slate-700'
                "
            >
                <i class="fa-solid fa-columns mr-1.5"></i> Split
            </button>
        </div>

        <!-- ==================== MAIN FORM ==================== -->
        <form
            @submit.prevent="submit"
            class="grid grid-cols-1 lg:grid-cols-12 gap-6"
        >
            <!-- ==================== EDITOR PANEL ==================== -->
            <div
                class="lg:col-span-7 xl:col-span-6 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit"
                :class="{ hidden: viewMode === 'preview' }"
            >
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4 bg-slate-50/50"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow"
                    >
                        <i class="fa-solid fa-pen text-sm"></i>
                    </span>
                    <div>
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800"
                        >
                            Editor
                        </h2>
                        <p class="text-xs text-slate-500">
                            Write your documentation here
                        </p>
                    </div>
                </div>

                <div class="p-5">
                    <RichTextEditor
                        v-model="form.docs"
                        label=""
                        placeholder="Start writing documentation..."
                        :error="form.errors.docs"
                        class="min-h-[400px]"
                    />

                    <div
                        class="mt-5 pt-4 border-t border-slate-100 flex justify-end"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-blue-600 hover:from-indigo-600 hover:to-blue-700 text-white font-bold py-2.5 px-6 shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed hover:shadow-xl"
                        >
                            <i
                                v-if="form.processing"
                                class="fa-solid fa-spinner fa-spin"
                            ></i>
                            <i v-else class="fa-solid fa-save"></i>
                            {{
                                form.processing
                                    ? "Saving..."
                                    : "Save Documentation"
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ==================== PREVIEW PANEL ==================== -->
            <div
                class="lg:col-span-5 xl:col-span-6 rounded-2xl bg-white shadow-md ring-1 ring-slate-100 overflow-hidden h-fit"
                :class="{
                    'hidden lg:block': viewMode === 'edit',
                    'lg:col-span-12 xl:col-span-12': viewMode === 'preview',
                }"
            >
                <div
                    class="flex items-center gap-2 border-b border-slate-100 px-5 py-4 bg-slate-50/50"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow"
                    >
                        <i class="fa-solid fa-eye text-sm"></i>
                    </span>
                    <div>
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800"
                        >
                            Live Preview
                        </h2>
                        <p class="text-xs text-slate-500">
                            How it will look to admins
                        </p>
                    </div>
                </div>

                <div class="p-5 sm:p-8">
                    <!-- Custom styles for the rendered HTML (prose) -->
                    <div
                        v-if="form.docs"
                        v-html="form.docs"
                        class="prose prose-slate prose-sm sm:prose-base max-w-none prose-headings:font-bold prose-headings:text-slate-800 prose-a:text-blue-600 hover:prose-a:text-blue-800 prose-img:rounded-lg prose-img:shadow-sm"
                    ></div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center py-20 text-center"
                    >
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4"
                        >
                            <i
                                class="fa-solid fa-file-word text-3xl text-slate-300"
                            ></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 mb-1">
                            No content yet
                        </h3>
                        <p class="text-sm text-slate-500">
                            Write in the editor to see the preview here.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </Layout>
</template>

<style scoped>
/* 
  Note: If you are using Tailwind CSS Typography plugin, 'prose' classes will work.
  If not, these fallback styles ensure the preview looks good regardless.
*/
.prose {
    color: #334155;
    line-height: 1.75;
}
.prose :deep(h1) {
    font-size: 1.875rem;
    font-weight: 800;
    color: #0f172a;
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.prose :deep(h2) {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 0.5rem;
}
.prose :deep(h3) {
    font-size: 1.25rem;
    font-weight: 600;
    color: #334155;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}
.prose :deep(p) {
    margin-bottom: 1.25rem;
}
.prose :deep(ul) {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 1.25rem;
}
.prose :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 1.25rem;
}
.prose :deep(li) {
    margin-bottom: 0.5rem;
}
.prose :deep(a) {
    color: #2563eb;
    text-decoration: underline;
}
.prose :deep(blockquote) {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    font-style: italic;
    color: #475569;
    background-color: #f8fafc;
    padding: 1rem;
    border-radius: 0 0.5rem 0.5rem 0;
    margin-bottom: 1.25rem;
}
.prose :deep(pre) {
    background-color: #1e293b;
    color: #f8fafc;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin-bottom: 1.25rem;
}
.prose :deep(code) {
    font-family: monospace;
    background-color: #f1f5f9;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    color: #db2777;
}
.prose :deep(img) {
    border-radius: 0.5rem;
    max-width: 100%;
    height: auto;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
