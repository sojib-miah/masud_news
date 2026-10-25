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
</script>

<template>
    <Layout>
        <Head title="Reporter Application Form" />

        <div class="m-auto w-[70%] border rounded shadow p-3 bg-white">
            <h2 class="text-2xl font-semibold font-robo text-center">
                সাংবাদিক প্রতিনিধি আবেদনপত্র
            </h2>
            <div class="font-robo">
                <p>
                    <span class="mb-2 block">বরাবর,</span>
                    <span class="mb-2 block">সম্পাদক,</span>
                    <span class="mb-2 block"
                        >আমার বাংলা মাল্টিমিডিয়া ও পাবলিকেশন লিমিটেড</span
                    >
                    ৯০/১, সিটি সেন্টার, মতিঝিল সি/এ, ঢাকা-১০০০ বাংলাদেশ <br />
                    <br />

                    বিষয়: অনলাইন প্রতিনিধি (Commission Based) হিসেবে
                    নিয়োগ–সংক্রান্ত আবেদন।
                </p>
                <!-- $page.props.auth?.reporterProfile?
                $page.props.auth?.user? -->
                <p>মাননীয় সম্পাদক/প্রকাশক মহোদয়,</p>
                <p class="leading-6 font-robo">
                    আমি নিম্নস্বাক্ষরকারী, নাম -
                    {{ $page.props.auth?.user?.name }}. পিতা-
                    {{ $page.props.auth?.reporterProfile?.father_name ?? "" }}
                    মাতা-
                    {{ $page.props.auth?.reporterProfile?.mother_name ?? "" }}
                    স্থায়ী ঠিকানা-
                    {{ $page.props.auth?.reporterProfile?.address ?? "" }} জাতীয়
                    পরিচয়পত্র/জন্ম নিবন্ধন নং-
                    {{ $page.props.auth?.reporterProfile?.nid_number ?? "" }}
                    মোবাইল নাম্বার-
                    {{ $page.props.auth?.user?.phone ?? "" }} . এই
                    মর্মে আবেদন করছি যে, আমি আপনার প্রতিষ্ঠানে অনলাইন প্রতিনিধি
                    (Commission Based) হিসেবে কাজ করতে আগ্রহী। আমি সম্পূর্ণ
                    কমিশন–ভিত্তিক পদ্ধতিতে, নিউজের ভিউ/রিচ অনুযায়ী পারিশ্রমিক
                    গ্রহণে সম্মত। প্রতিষ্ঠানের উন্নয়ন, তথ্যপ্রবাহ বৃদ্ধিকরণ এবং
                    সঠিক সংবাদ পরিবেশনে দায়িত্বশীল ভূমিকা রাখতে চাই। আপনার
                    প্রতিষ্ঠানের নীতি, মানদণ্ড এবং সম্পাদকীয় নির্দেশনা অনুসরণ
                    করে নিয়মিত নিউজ প্রেরণ ও জনস্বার্থে প্রতিবেদন তৈরি করার
                    অঙ্গীকার করছি।
                </p>
                <p>অঙ্গীকারনামা (Commission Based Reporter Agreement)</p>
                <p>আমি ঘোষণা করছি যে—</p>
                <ul class="leading-6 mb-3">
                    <li>
                        আইন, নীতিমালা ও নৈতিকতা
                        <ul>
                            <li>
                                আমি বাংলাদেশের প্রচলিত আইন, সাংবাদিকতার নৈতিকতা
                                ও প্রতিষ্ঠানের সম্পাদকীয় নীতিমালা মেনে দায়িত্ব
                                পালন করব।
                            </li>
                            <li>
                                আমি কোনো ভুয়া, বিভ্রান্তিকর, গুজবভিত্তিক বা
                                প্রমাণহীন তথ্য প্রকাশ করব না।
                            </li>
                            <li>
                                আমি কোনোরূপ অপপ্রচার, ব্যক্তিগত আক্রমণ,
                                মানহানিকর বা বেআইনি কনটেন্ট তৈরি করব না।
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="leading-6 mb-3">
                    <li>
                        কমিশন–ভিত্তিক কাজের শর্ত
                        <ul>
                            <li>
                                আমি বেতনভুক্ত নই; নিউজের ভিউ/রিচ/ইনগেজমেন্ট
                                অনুযায়ী কমিশন পদ্ধতিতে কাজ করব।
                            </li>
                            <li>
                                প্রতিষ্ঠানের নির্ধারিত কমিশন হার, নিয়মনীতি ও
                                পেমেন্ট প্রক্রিয়া আমি মান্য করব।
                            </li>
                            <li>
                                আমার প্রেরিত নিউজের ভিউ–ডাটা প্রতিষ্ঠান যাচাই
                                করে কমিশন নির্ধারণ করবে—এ বিষয়ে আমি সম্মত।
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="leading-6 mb-3">
                    <li>
                        দায়িত্ববোধ ও পেশাগত আচরণ
                        <ul>
                            <li>
                                সংবাদ সংগ্রহ ও প্রকাশের ক্ষেত্রে আমি সততা,
                                নিরপেক্ষতা, জনস্বার্থ ও পেশাদারিত্ব বজায় রাখব।
                            </li>
                            <li>
                                প্রতিষ্ঠানের নাম, পরিচয় বা আইডি ব্যবহার করে কোনো
                                ব্যক্তিগত সুবিধা, আর্থিক লেনদেন বা বেআইনি প্রভাব
                                খাটাব না।
                            </li>
                            <li>
                                অফিসিয়াল তথ্য, সোর্স বা গোপন নথি অনুমতি ছাড়া
                                প্রকাশ করব না।
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="leading-6 mb-3">
                    <li>
                        আর্থিক ও প্রশাসনিক নীতিমালা
                        <ul>
                            <li>
                                প্রতিষ্ঠানের মাসিক সদস্যপদ চাঁদা, মার্কেটিং ফি
                                বা প্রশাসনিক চার্জ (যদি থাকে) নিয়মিত পরিশোধ করতে
                                আমি সম্মত।
                            </li>
                            <li>
                                কমিশনের বাইরে অতিরিক্ত কোনো আর্থিক দাবি বা
                                সুবিধা চাইব না।
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="leading-6 mb-3">
                    <li>
                        শাস্তিমূলক ও চুক্তিভঙ্গ সংক্রান্ত শর্ত
                        <ul>
                            <li>
                                আমি যদি প্রতিষ্ঠানের নীতিমালা ভঙ্গ করি, মিথ্যা
                                তথ্য প্রদান করি বা বেআইনি কর্মকাণ্ডে জড়াই—তাহলে
                                আমার সদস্যপদ বা প্রতিনিধি আইডি বাতিল করা যাবে
                                এবং এ বিষয়ে আমি আপত্তি করব না।
                            </li>
                            <li>
                                প্রয়োজন হলে আমার বিরুদ্ধে আইনগত ব্যবস্থা নেওয়া
                                যেতে পারে—এ বিষয়ে আমি নীতিগতভাবে সম্মত।
                            </li>
                        </ul>
                    </li>
                </ul>
                <p>ঘোষণা</p>
                <p class="mb-5">
                    উপরোক্ত সকল শর্ত আমি পূর্ণাঙ্গভাবে বুঝেছি, স্বেচ্ছায় গ্রহণ
                    করেছি এবং নির্ধারিত নীতি অনুযায়ী কমিশন ভিত্তিক
                    সংবাদদাতা/প্রতিনিধি হিসেবে কাজ করতে অঙ্গীকার করছি।
                </p>
                <p>ধন্যবাদান্তে,</p>
                <p>
                    <img
                        :src="
                            $page.props.auth?.reporterProfile?.signature
                                ? '/storage/' +
                                  $page.props.auth.reporterProfile.signature
                                : '/storage/images/user.png'
                        "
                        alt="user image"
                        class="h-8 w-8 rounded-full object-cover"
                    />
                </p>
                <span class="border-t pt-1 mb-2">আবেদনকারীর স্বাক্ষর</span>
                <p class="mt-5">তারিখ: {{ today }}</p>
            </div>
        </div>
    </Layout>
</template>
