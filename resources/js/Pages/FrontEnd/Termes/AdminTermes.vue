<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import RichTextEditor from "@/Shared/RichTextEditor.vue";
import Layout from "@/Shared/Layout.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
import FlashMessage from "@/Shared/FlashMessage.vue";
import { ref, computed } from "vue";

const props = defineProps({
    term: { type: Object, default: null },
});

// `editing` is derived from whether the record already exists
const editing = computed(() => !!(props.term && props.term.id));

const form = useForm({
    termes: props.term?.termes || "",
    privacy: props.term?.privacy || "",
});

// Track which editor is active (for nice focus feedback)
const activeTab = ref("termes"); // 'termes' | 'privacy'

function submit() {
    form.post(route("terms.storeOrUpdate"), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Layout>
        <Head title="Terms & Conditions" />

        <FlashMessage
            v-if="$page.props.flash?.status"
            :message="$page.props.flash.status"
            type="success"
        />

        <Breadcrumb />

        <div class="min-h-screen bg-slate-50">
            <div>
                <!-- ==================== HEADER ==================== -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-lg px-5 sm:px-8 py-5 sm:py-6 mb-5 sm:mb-6"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="bg-white/20 backdrop-blur rounded-xl p-3"
                            >
                                <i
                                    class="fa-solid fa-file-contract text-white text-xl sm:text-2xl"
                                ></i>
                            </div>
                            <div>
                                <h1
                                    class="text-white text-xl sm:text-2xl font-bold"
                                >
                                    Terms & Privacy Editor
                                </h1>
                                <p
                                    class="text-green-100 text-xs sm:text-sm mt-0.5"
                                >
                                    শর্তাবলী ও প্রাইভেসি পলিসি সম্পাদনা করুন
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-white/15 backdrop-blur rounded-xl px-4 py-2 text-center"
                        >
                            <p
                                class="text-green-100 text-[10px] sm:text-xs uppercase tracking-wide"
                            >
                                Mode
                            </p>
                            <p
                                class="text-white text-sm sm:text-base font-bold leading-tight"
                            >
                                {{ editing ? "Editing" : "Creating" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ==================== MAIN FORM CARD ==================== -->
                <form
                    @submit.prevent="submit"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                >
                    <!-- Card header -->
                    <div
                        class="px-4 sm:px-6 py-4 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2"
                    >
                        <h2
                            class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2"
                        >
                            <i
                                class="fa-solid fa-pen-to-square text-green-600"
                            ></i>
                            Content Editor
                        </h2>
                        <span
                            class="text-xs font-semibold px-3 py-1 rounded-full"
                            :class="
                                editing
                                    ? 'bg-amber-50 text-amber-700'
                                    : 'bg-green-50 text-green-700'
                            "
                        >
                            <i
                                :class="
                                    editing
                                        ? 'fa-solid fa-pen'
                                        : 'fa-solid fa-plus'
                                "
                                class="mr-1"
                            ></i>
                            {{ editing ? "Editing existing" : "New entry" }}
                        </span>
                    </div>

                    <!-- ==================== TAB SWITCHER (mobile) ==================== -->
                    <div class="px-4 pt-4">
                        <div
                            class="grid grid-cols-2 gap-2 bg-slate-100 rounded-lg p-1"
                        >
                            <button
                                type="button"
                                @click="activeTab = 'termes'"
                                class="py-2 rounded-md text-sm font-semibold transition flex items-center justify-center gap-1.5"
                                :class="
                                    activeTab === 'termes'
                                        ? 'bg-white text-green-700 shadow-sm'
                                        : 'text-slate-500'
                                "
                            >
                                <i class="fa-solid fa-file-lines"></i>
                                Terms
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'privacy'"
                                class="py-2 rounded-md text-sm font-semibold transition flex items-center justify-center gap-1.5"
                                :class="
                                    activeTab === 'privacy'
                                        ? 'bg-white text-green-700 shadow-sm'
                                        : 'text-slate-500'
                                "
                            >
                                <i class="fa-solid fa-shield-halved"></i>
                                Privacy
                            </button>
                        </div>
                    </div>

                    <!-- ==================== EDITORS ==================== -->
                    <div class="p-4 sm:p-6">
                        <div>
                            <!-- TERMS -->
                            <div
                                :class="{
                                    hidden:
                                        activeTab !== 'termes' && 'lg:hidden',
                                    block: activeTab === 'termes' || 'lg:block',
                                }"
                                class="flex flex-col"
                            >
                                <label
                                    class="flex items-center gap-2 text-sm font-bold text-slate-700 mb-2 pb-2 border-b border-slate-100"
                                >
                                    <span
                                        class="w-7 h-7 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0"
                                    >
                                        <i
                                            class="fa-solid fa-file-lines text-xs"
                                        ></i>
                                    </span>
                                    Terms & Conditions
                                </label>

                                <div
                                    class="flex-1 rounded-lg overflow-hidden border border-slate-200 focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-500/20 transition"
                                >
                                    <RichTextEditor
                                        v-model="form.termes"
                                        placeholder="Enter your terms & conditions here..."
                                        :error="form.errors.termes"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.termes"
                                    class="text-red-500 text-xs mt-1.5 flex items-center gap-1"
                                >
                                    <i
                                        class="fa-solid fa-circle-exclamation"
                                    ></i>
                                    {{ form.errors.termes }}
                                </p>
                                <p v-else class="text-xs text-slate-400 mt-1.5">
                                    <i class="fa-solid fa-circle-info mr-1"></i>
                                    এই অংশটি ওয়েবসাইটের Terms পেজে দেখানো হবে
                                </p>
                            </div>

                            <!-- PRIVACY -->
                            <div
                                :class="{
                                    hidden:
                                        activeTab !== 'privacy' && 'lg:hidden',
                                    block:
                                        activeTab === 'privacy' || 'lg:block',
                                }"
                                class="flex flex-col"
                            >
                                <label
                                    class="flex items-center gap-2 text-sm font-bold text-slate-700 mb-2 pb-2 border-b border-slate-100"
                                >
                                    <span
                                        class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0"
                                    >
                                        <i
                                            class="fa-solid fa-shield-halved text-xs"
                                        ></i>
                                    </span>
                                    Privacy Policy
                                </label>

                                <div
                                    class="flex-1 rounded-lg overflow-hidden border border-slate-200 focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-500/20 transition"
                                >
                                    <RichTextEditor
                                        v-model="form.privacy"
                                        placeholder="Enter your privacy policy here..."
                                        :error="form.errors.privacy"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.privacy"
                                    class="text-red-500 text-xs mt-1.5 flex items-center gap-1"
                                >
                                    <i
                                        class="fa-solid fa-circle-exclamation"
                                    ></i>
                                    {{ form.errors.privacy }}
                                </p>
                                <p v-else class="text-xs text-slate-400 mt-1.5">
                                    <i class="fa-solid fa-circle-info mr-1"></i>
                                    এই অংশটি ওয়েবসাইটের Privacy পেজে দেখানো হবে
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== FOOTER / SUBMIT ==================== -->
                    <div
                        class="px-4 sm:px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <p
                            class="text-xs text-slate-500 text-center sm:text-left"
                        >
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            উভয় ফিল্ড ঐচ্ছিক — যা লিখবেন তাই সেভ হবে
                        </p>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-2.5 px-8 rounded-lg shadow-md transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <i
                                v-if="form.processing"
                                class="fa-solid fa-spinner fa-spin"
                            ></i>
                            <i
                                v-else
                                :class="
                                    editing
                                        ? 'fa-solid fa-floppy-disk'
                                        : 'fa-solid fa-plus'
                                "
                            ></i>
                            {{
                                form.processing
                                    ? "Saving..."
                                    : editing
                                      ? "Update"
                                      : "Create"
                            }}
                        </button>
                    </div>
                </form>

                <!-- Hint -->
                <div
                    class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-3"
                >
                    <i
                        class="fa-solid fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"
                    ></i>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        <strong>টিপস:</strong> রিচ টেক্সট এডিটর ব্যবহার করে
                        বোল্ড, ইটালিক, লিস্ট ইত্যাদি ফরম্যাট করতে পারবেন। সেভ
                        করলে উভয় ফিল্ড একসাথে আপডেট হয়।
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>
