<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import { ref } from "vue";

const today = ref(
    new Date().toLocaleDateString("bn-BD", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }),
);

// Optional: print action
function printForm() {
    window.print();
}
</script>

<template>
    <Layout>
        <Head title="Reporter Application Form" />

        <div
            class="min-h-screen bg-slate-50 print:bg-white print:py-0 print:px-0"
        >
            <div class="max-w-5xl mx-auto py-6 px-3 sm:px-5">
                <!-- ==================== HEADER BANNER ==================== -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-2xl shadow-xl overflow-hidden print:hidden"
                >
                    <div
                        class="px-5 sm:px-8 py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-xl"
                            >
                                <i class="fa-solid fa-file-signature"></i>
                            </div>
                            <div>
                                <h1
                                    class="text-white text-lg sm:text-2xl font-bold leading-tight"
                                >
                                    প্রতিনিধি আবেদনপত্র
                                </h1>
                                <p
                                    class="text-green-100 text-xs sm:text-sm mt-0.5"
                                >
                                    Reporter Application Form
                                </p>
                            </div>
                        </div>

                        <button
                            @click="printForm"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 bg-white text-green-700 hover:bg-green-50 font-semibold px-4 py-2 rounded-lg shadow transition text-sm w-full sm:w-auto"
                        >
                            <i class="fa-solid fa-print"></i>
                            প্রিন্ট করুন
                        </button>
                    </div>
                </div>

                <!-- ==================== DOCUMENT ==================== -->
                <div
                    class="bg-white rounded-b-2xl shadow-xl border border-t-0 border-slate-200 overflow-hidden print:rounded-none print:shadow-none print:border-0"
                >
                    <div class="p-5 sm:p-8 lg:p-10">
                        <!-- Document title -->
                        <div
                            class="text-center mb-6 pb-5 border-b-2 border-dashed border-green-200"
                        >
                            <h2
                                class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 leading-tight"
                            >
                                সাংবাদিক প্রতিনিধি আবেদনপত্র
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-500 mt-2">
                                অনলাইন প্রতিনিধি (Commission Based) — নিয়োগ
                                সংক্রান্ত
                            </p>
                        </div>

                        <div
                            class="font-robo text-slate-700 text-sm sm:text-base leading-7"
                        >
                            <!-- ==================== ADDRESS ==================== -->
                            <div class="mb-5">
                                <p class="mb-1">বরাবর,</p>
                                <p class="mb-1 font-semibold text-slate-900">
                                    সম্পাদক,
                                </p>
                                <p class="mb-1 font-semibold text-slate-900">
                                    বাংলাদেশ নিউজ টুডে মাল্টিমিডিয়া ও পাবলিকেশন
                                    লিমিটেড
                                </p>
                                <p class="text-slate-600">
                                    ৯০/১, সিটি সেন্টার, মতিঝিল সি/এ, ঢাকা-১০০০
                                    বাংলাদেশ
                                </p>
                            </div>

                            <!-- ==================== SUBJECT ==================== -->
                            <div
                                class="mb-6 bg-green-50 border-l-4 border-green-600 rounded-r-lg px-4 py-3"
                            >
                                <p class="font-semibold text-green-800">
                                    <span class="text-green-700">বিষয়:</span>
                                    অনলাইন প্রতিনিধি (Commission Based) হিসেবে
                                    নিয়োগ–সংক্রান্ত আবেদন।
                                </p>
                            </div>

                            <!-- ==================== SALUTATION ==================== -->
                            <p class="mb-3 font-semibold text-slate-800">
                                মাননীয় সম্পাদক/প্রকাশক মহোদয়,
                            </p>

                            <!-- ==================== INTRO PARAGRAPH ==================== -->
                            <p class="mb-5 text-justify">
                                আমি নিম্নস্বাক্ষরকারী, নাম -
                                <strong class="text-slate-900">{{
                                    $page.props.auth?.user?.name
                                }}</strong
                                >. পিতা-
                                <strong class="text-slate-900">{{
                                    $page.props.auth?.reporterProfile
                                        ?.father_name ?? ""
                                }}</strong>
                                মাতা-
                                <strong class="text-slate-900">{{
                                    $page.props.auth?.reporterProfile
                                        ?.mother_name ?? ""
                                }}</strong>
                                স্থায়ী ঠিকানা-
                                <strong class="text-slate-900">{{
                                    $page.props.auth?.reporterProfile
                                        ?.address ?? ""
                                }}</strong>
                                জাতীয় পরিচয়পত্র/জন্ম নিবন্ধন নং-
                                <strong class="text-slate-900">{{
                                    $page.props.auth?.reporterProfile
                                        ?.nid_number ?? ""
                                }}</strong>
                                মোবাইল নাম্বার-
                                <strong class="text-slate-900">{{
                                    $page.props.auth?.user?.phone ?? ""
                                }}</strong
                                >. এই মর্মে আবেদন করছি যে, আমি আপনার প্রতিষ্ঠানে
                                অনলাইন প্রতিনিধি (Commission Based) হিসেবে কাজ
                                করতে আগ্রহী। আমি সম্পূর্ণ কমিশন–ভিত্তিক
                                পদ্ধতিতে, নিউজের ভিউ/রিচ অনুযায়ী পারিশ্রমিক
                                গ্রহণে সম্মত। প্রতিষ্ঠানের উন্নয়ন, তথ্যপ্রবাহ
                                বৃদ্ধিকরণ এবং সঠিক সংবাদ পরিবেশনে দায়িত্বশীল
                                ভূমিকা রাখতে চাই। আপনার প্রতিষ্ঠানের নীতি,
                                মানদণ্ড এবং সম্পাদকীয় নির্দেশনা অনুসরণ করে
                                নিয়মিত নিউজ প্রেরণ ও জনস্বার্থে প্রতিবেদন তৈরি
                                করার অঙ্গীকার করছি।
                            </p>

                            <!-- ==================== AGREEMENT TITLE ==================== -->
                            <div
                                class="text-center my-6 py-3 bg-gradient-to-r from-green-50 via-white to-green-50 rounded-lg border border-green-100"
                            >
                                <h3
                                    class="text-base sm:text-lg font-bold text-green-800"
                                >
                                    <i class="fa-solid fa-handshake mr-2"></i>
                                    অঙ্গীকারনামা (Commission Based Reporter
                                    Agreement)
                                </h3>
                            </div>

                            <p class="mb-4 font-semibold text-slate-800">
                                আমি ঘোষণা করছি যে—
                            </p>

                            <!-- ==================== SECTION 1 ==================== -->
                            <div class="mb-4 pl-4 border-l-3 border-green-500">
                                <h4
                                    class="font-bold text-slate-900 mb-2 flex items-center gap-2"
                                >
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-600 text-white text-xs font-bold"
                                        >১</span
                                    >
                                    আইন, নীতিমালা ও নৈতিকতা
                                </h4>
                                <ul
                                    class="space-y-1.5 text-justify list-disc pl-5 marker:text-green-600"
                                >
                                    <li>
                                        আমি বাংলাদেশের প্রচলিত আইন, সাংবাদিকতার
                                        নৈতিকতা ও প্রতিষ্ঠানের সম্পাদকীয়
                                        নীতিমালা মেনে দায়িত্ব পালন করব।
                                    </li>
                                    <li>
                                        আমি কোনো ভুয়া, বিভ্রান্তিকর,
                                        গুজবভিত্তিক বা প্রমাণহীন তথ্য প্রকাশ করব
                                        না।
                                    </li>
                                    <li>
                                        আমি কোনোরূপ অপপ্রচার, ব্যক্তিগত আক্রমণ,
                                        মানহানিকর বা বেআইনি কনটেন্ট তৈরি করব না।
                                    </li>
                                </ul>
                            </div>

                            <!-- ==================== SECTION 2 ==================== -->
                            <div class="mb-4 pl-4 border-l-3 border-green-500">
                                <h4
                                    class="font-bold text-slate-900 mb-2 flex items-center gap-2"
                                >
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-600 text-white text-xs font-bold"
                                        >২</span
                                    >
                                    কমিশন–ভিত্তিক কাজের শর্ত
                                </h4>
                                <ul
                                    class="space-y-1.5 text-justify list-disc pl-5 marker:text-green-600"
                                >
                                    <li>
                                        আমি বেতনভুক্ত নই; নিউজের
                                        ভিউ/রিচ/ইনগেজমেন্ট অনুযায়ী কমিশন
                                        পদ্ধতিতে কাজ করব।
                                    </li>
                                    <li>
                                        প্রতিষ্ঠানের নির্ধারিত কমিশন হার,
                                        নিয়মনীতি ও পেমেন্ট প্রক্রিয়া আমি মান্য
                                        করব।
                                    </li>
                                    <li>
                                        আমার প্রেরিত নিউজের ভিউ–ডাটা প্রতিষ্ঠান
                                        যাচাই করে কমিশন নির্ধারণ করবে—এ বিষয়ে
                                        আমি সম্মত।
                                    </li>
                                </ul>
                            </div>

                            <!-- ==================== SECTION 3 ==================== -->
                            <div class="mb-4 pl-4 border-l-3 border-green-500">
                                <h4
                                    class="font-bold text-slate-900 mb-2 flex items-center gap-2"
                                >
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-600 text-white text-xs font-bold"
                                        >৩</span
                                    >
                                    দায়িত্ববোধ ও পেশাগত আচরণ
                                </h4>
                                <ul
                                    class="space-y-1.5 text-justify list-disc pl-5 marker:text-green-600"
                                >
                                    <li>
                                        সংবাদ সংগ্রহ ও প্রকাশের ক্ষেত্রে আমি
                                        সততা, নিরপেক্ষতা, জনস্বার্থ ও
                                        পেশাদারিত্ব বজায় রাখব।
                                    </li>
                                    <li>
                                        প্রতিষ্ঠানের নাম, পরিচয় বা আইডি ব্যবহার
                                        করে কোনো ব্যক্তিগত সুবিধা, আর্থিক লেনদেন
                                        বা বেআইনি প্রভাব খাটাব না।
                                    </li>
                                    <li>
                                        অফিসিয়াল তথ্য, সোর্স বা গোপন নথি অনুমতি
                                        ছাড়া প্রকাশ করব না।
                                    </li>
                                </ul>
                            </div>

                            <!-- ==================== SECTION 4 ==================== -->
                            <div class="mb-4 pl-4 border-l-3 border-green-500">
                                <h4
                                    class="font-bold text-slate-900 mb-2 flex items-center gap-2"
                                >
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-600 text-white text-xs font-bold"
                                        >৪</span
                                    >
                                    আর্থিক ও প্রশাসনিক নীতিমালা
                                </h4>
                                <ul
                                    class="space-y-1.5 text-justify list-disc pl-5 marker:text-green-600"
                                >
                                    <li>
                                        প্রতিষ্ঠানের মাসিক সদস্যপদ চাঁদা,
                                        মার্কেটিং ফি বা প্রশাসনিক চার্জ (যদি
                                        থাকে) নিয়মিত পরিশোধ করতে আমি সম্মত।
                                    </li>
                                    <li>
                                        কমিশনের বাইরে অতিরিক্ত কোনো আর্থিক দাবি
                                        বা সুবিধা চাইব না।
                                    </li>
                                </ul>
                            </div>

                            <!-- ==================== SECTION 5 ==================== -->
                            <div class="mb-5 pl-4 border-l-3 border-green-500">
                                <h4
                                    class="font-bold text-slate-900 mb-2 flex items-center gap-2"
                                >
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-600 text-white text-xs font-bold"
                                        >৫</span
                                    >
                                    শাস্তিমূলক ও চুক্তিভঙ্গ সংক্রান্ত শর্ত
                                </h4>
                                <ul
                                    class="space-y-1.5 text-justify list-disc pl-5 marker:text-green-600"
                                >
                                    <li>
                                        আমি যদি প্রতিষ্ঠানের নীতিমালা ভঙ্গ করি,
                                        মিথ্যা তথ্য প্রদান করি বা বেআইনি
                                        কর্মকাণ্ডে জড়াই—তাহলে আমার সদস্যপদ বা
                                        প্রতিনিধি আইডি বাতিল করা যাবে এবং এ
                                        বিষয়ে আমি আপত্তি করব না।
                                    </li>
                                    <li>
                                        প্রয়োজন হলে আমার বিরুদ্ধে আইনগত
                                        ব্যবস্থা নেওয়া যেতে পারে—এ বিষয়ে আমি
                                        নীতিগতভাবে সম্মত।
                                    </li>
                                </ul>
                            </div>

                            <!-- ==================== DECLARATION ==================== -->
                            <div
                                class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6"
                            >
                                <h4
                                    class="font-bold text-amber-800 mb-2 flex items-center gap-2"
                                >
                                    <i class="fa-solid fa-scroll"></i>
                                    ঘোষণা
                                </h4>
                                <p class="text-justify text-slate-700">
                                    উপরোক্ত সকল শর্ত আমি পূর্ণাঙ্গভাবে বুঝেছি,
                                    স্বেচ্ছায় গ্রহণ করেছি এবং নির্ধারিত নীতি
                                    অনুযায়ী কমিশন ভিত্তিক সংবাদদাতা/প্রতিনিধি
                                    হিসেবে কাজ করতে অঙ্গীকার করছি।
                                </p>
                            </div>

                            <!-- ==================== SIGNATURE ==================== -->
                            <div
                                class="mt-8 pt-6 border-t border-dashed border-slate-300"
                            >
                                <p class="mb-6 font-semibold text-slate-800">
                                    ধন্যবাদান্তে,
                                </p>

                                <div
                                    class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6"
                                >
                                    <!-- Signature block -->
                                    <div class="flex flex-col items-start">
                                        <div class="mb-2">
                                            <img
                                                :src="
                                                    $page.props.auth
                                                        ?.reporterProfile
                                                        ?.signature
                                                        ? '/storage/' +
                                                          $page.props.auth
                                                              .reporterProfile
                                                              .signature
                                                        : '/storage/images/user.png'
                                                "
                                                alt="Signature"
                                                class="h-16 w-32 object-contain rounded border border-slate-200 bg-white p-1"
                                            />
                                        </div>
                                        <div
                                            class="border-t-2 border-slate-400 pt-1 w-48"
                                        >
                                            <p
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                আবেদনকারীর স্বাক্ষর
                                            </p>
                                            <p
                                                class="text-xs text-slate-500 mt-0.5"
                                            >
                                                {{
                                                    $page.props.auth?.user?.name
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Date block -->
                                    <div
                                        class="flex items-center gap-2 text-slate-700"
                                    >
                                        <i
                                            class="fa-solid fa-calendar-days text-green-600"
                                        ></i>
                                        <span class="font-semibold"
                                            >তারিখ:</span
                                        >
                                        <span
                                            class="border-b border-dotted border-slate-400 pb-0.5 px-1"
                                        >
                                            {{ today }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== FOOTER NOTE ==================== -->
                <p class="text-center text-xs text-slate-400 mt-4 print:hidden">
                    <i class="fa-solid fa-lock mr-1"></i>
                    This document is confidential and generated for reporting
                    purposes only.
                </p>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* Custom font for Bengali */
.font-robo {
    font-family:
        "Hind Siliguri", "Noto Sans Bengali", "SolaimanLipi", system-ui,
        sans-serif;
}

/* Left border thickness used in sections */
.border-l-3 {
    border-left-width: 3px;
}

/* Print optimization */
@media print {
    body {
        background: #fff !important;
    }
    .print\:hidden {
        display: none !important;
    }
    .print\:bg-white {
        background: #fff !important;
    }
    .print\:rounded-none {
        border-radius: 0 !important;
    }
    .print\:shadow-none {
        box-shadow: none !important;
    }
    .print\:border-0 {
        border: none !important;
    }
    @page {
        margin: 1.5cm;
    }
}

/* Mobile tweaks */
@media (max-width: 640px) {
    .font-robo ul li {
        font-size: 13px;
        line-height: 1.75;
    }
}
</style>
