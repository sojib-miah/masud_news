<script setup>
import { Head, Link } from "@inertiajs/vue3";
import Layout from "@/Components/Layout.vue";
import { route } from "ziggy-js";
import { computed } from "vue";

const props = defineProps({
    reporter: { type: Object, required: true },
});

// Friendly label for the status badge
const statusLabel = computed(() => {
    const s = (props.reporter.status || "").toLowerCase();
    if (s === "approved") return "Verified Reporter";
    if (s === "pending") return "Pending Approval";
    if (s === "rejected") return "Rejected";
    return "Unverified";
});

const statusClasses = computed(() => {
    const s = (props.reporter.status || "").toLowerCase();
    if (s === "approved") return "bg-green-100 text-green-700 border-green-300";
    if (s === "pending") return "bg-amber-100 text-amber-700 border-amber-300";
    if (s === "rejected") return "bg-red-100 text-red-700 border-red-300";
    return "bg-slate-100 text-slate-600 border-slate-300";
});

const statusIcon = computed(() => {
    const s = (props.reporter.status || "").toLowerCase();
    if (s === "approved") return "fa-solid fa-circle-check";
    if (s === "pending") return "fa-solid fa-hourglass-half";
    if (s === "rejected") return "fa-solid fa-circle-xmark";
    return "fa-solid fa-circle-question";
});
</script>

<template>
    <Layout>
        <Head :title="props.reporter.name" />

        <div class="py-8">
            <div class="max-w-5xl mx-auto px-3 sm:px-4 lg:px-6">
                <!-- Back -->
                <Link
                    :href="route('our.family')"
                    class="inline-flex items-center text-green-700 hover:text-green-800 text-sm font-semibold mb-4"
                >
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    ফিরে যান
                </Link>

                <!-- Not approved alert banner -->
                <div
                    v-if="!props.reporter.is_approved"
                    class="mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 shadow-sm"
                    :class="statusClasses"
                >
                    <i :class="[statusIcon, 'text-lg mt-0.5']"></i>
                    <div class="flex-1">
                        <p class="font-bold text-sm">
                            {{ statusLabel }}
                        </p>
                        <p class="text-xs mt-0.5 opacity-90">
                            এই রিপোর্টারের প্রোফাইল এখনো সম্পূর্ণভাবে যাচাইকৃত
                            হয়নি। তথ্যগুলো প্রাথমিকভাবে প্রদর্শিত হচ্ছে।
                        </p>
                    </div>
                </div>

                <!-- Main card -->
                <div
                    class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden"
                >
                    <!-- ================= HERO ================= -->
                    <div
                        class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-6 relative"
                    >
                        <div
                            class="flex flex-col sm:flex-row items-center sm:items-end gap-5"
                        >
                            <!-- Image -->
                            <div class="relative">
                                <div
                                    class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-lg bg-white"
                                    :class="
                                        props.reporter.is_approved
                                            ? ''
                                            : 'grayscale brightness-90'
                                    "
                                >
                                    <img
                                        v-if="props.reporter.photo_url"
                                        :src="props.reporter.photo_url"
                                        :alt="props.reporter.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-green-700 text-4xl font-bold"
                                    >
                                        {{
                                            (props.reporter.name || "?").charAt(
                                                0,
                                            )
                                        }}
                                    </div>
                                </div>

                                <!-- Verified tick -->
                                <span
                                    v-if="props.reporter.is_approved"
                                    class="absolute -bottom-1 -right-1 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center border-4 border-white shadow"
                                    title="Verified"
                                >
                                    <i
                                        class="fa-solid fa-check text-white text-xs"
                                    ></i>
                                </span>
                            </div>

                            <!-- Name + types + status -->
                            <div class="flex-1 text-center sm:text-left">
                                <h1
                                    class="text-2xl sm:text-3xl font-bold text-white"
                                >
                                    {{ props.reporter.name }}
                                </h1>

                                <!-- Type chips -->
                                <div
                                    v-if="props.reporter.type_names?.length"
                                    class="flex flex-wrap justify-center sm:justify-start gap-1.5 mt-2"
                                >
                                    <span
                                        v-for="(tn, i) in props.reporter
                                            .type_names"
                                        :key="i"
                                        class="bg-white/20 backdrop-blur-sm text-white text-xs px-2.5 py-0.5 rounded-full"
                                    >
                                        <i class="fa-solid fa-tag mr-1"></i
                                        >{{ tn }}
                                    </span>
                                </div>

                                <!-- Status pill -->
                                <div
                                    class="mt-3 flex justify-center sm:justify-start"
                                >
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border"
                                        :class="statusClasses"
                                    >
                                        <i :class="statusIcon"></i>
                                        {{ statusLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= BODY ================= -->
                    <div class="p-6 space-y-8">
                        <!-- Contact -->
                        <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-address-card mr-2"></i
                                >যোগাযোগ
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- <div
                                    v-if="props.reporter.phone"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-phone text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">ফোন</p>
                                        <p class="value">
                                            <a
                                                :href="`tel:${props.reporter.phone}`"
                                                class="hover:text-green-700"
                                            >
                                                {{ props.reporter.phone }}
                                            </a>
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.email"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-envelope text-green-600"
                                    ></i>
                                    <div class="min-w-0">
                                        <p class="label">ইমেইল</p>
                                        <p class="value break-all">
                                            <a
                                                :href="`mailto:${props.reporter.email}`"
                                                class="hover:text-green-700"
                                            >
                                                {{ props.reporter.email }}
                                            </a>
                                        </p>
                                    </div>
                                </div> -->

                                <div
                                    v-if="props.reporter.location"
                                    class="info-tile sm:col-span-2"
                                >
                                    <i
                                        class="fa-solid fa-location-dot text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label mb-2">ঠিকানা</p>
                                        <p class="value">
                                            {{ props.reporter.location }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.address"
                                    class="info-tile sm:col-span-2"
                                >
                                    <i
                                        class="fa-solid fa-house text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">বিস্তারিত ঠিকানা</p>
                                        <p class="value">
                                            {{ props.reporter.address }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Personal -->
                        <!-- <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-user mr-2"></i>ব্যক্তিগত
                                তথ্য
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div
                                    v-if="props.reporter.dob"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-calendar text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">জন্ম তারিখ</p>
                                        <p class="value">
                                            {{ props.reporter.dob }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.blood_group"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-droplet text-red-500"
                                    ></i>
                                    <div>
                                        <p class="label">রক্তের গ্রুপ</p>
                                        <p class="value">
                                            {{ props.reporter.blood_group }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.father_name"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-male text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">পিতার নাম</p>
                                        <p class="value">
                                            {{ props.reporter.father_name }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.mother_name"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-female text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">মাতার নাম</p>
                                        <p class="value">
                                            {{ props.reporter.mother_name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section> -->

                        <!-- Professional -->
                        <!-- <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-briefcase mr-2"></i>পেশাগত
                                তথ্য
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div
                                    v-if="props.reporter.reporter_area"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-map-location-dot text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">রিপোর্টার এরিয়া</p>
                                        <p class="value">
                                            {{ props.reporter.reporter_area }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.education"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-graduation-cap text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">শিক্ষাগত যোগ্যতা</p>
                                        <p class="value">
                                            {{ props.reporter.education }}
                                            <span
                                                v-if="
                                                    props.reporter
                                                        .education_year
                                                "
                                                class="text-slate-400 text-xs font-normal"
                                            >
                                                ({{
                                                    props.reporter
                                                        .education_year
                                                }})
                                            </span>
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.joined_at"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-calendar-check text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">যোগদানের তারিখ</p>
                                        <p class="value">
                                            {{ props.reporter.joined_at }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="props.reporter.nid_number"
                                    class="info-tile"
                                >
                                    <i
                                        class="fa-solid fa-id-card text-green-600"
                                    ></i>
                                    <div>
                                        <p class="label">এনআইডি নম্বর</p>
                                        <p class="value">
                                            {{ props.reporter.nid_number }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section> -->

                        <!-- Signature -->
                        <!-- <section>
                            <h3
                                class="text-sm font-bold text-green-700 uppercase mb-3 pb-2 border-b border-green-200"
                            >
                                <i class="fa-solid fa-signature mr-2"></i
                                >স্বাক্ষর & এনআইডি / পাসপোর্ট / জন্ম নিবন্ধন
                            </h3>
                            <div
                                class="flex gap-5 border border-slate-200 rounded-lg p-3 bg-white"
                            >
                                <img
                                    :src="props.reporter.signature_url"
                                    alt="Signature"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                    class="h-16 object-contain"
                                />
                                <img
                                    :src="props.reporter.nid_url"
                                    alt="nid"
                                    @error="
                                        (e) =>
                                            (e.target.src =
                                                '/images/default.jpg')
                                    "
                                    class="h-16 object-contain"
                                />
                            </div>
                        </section> -->
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.info-tile {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border: 1px solid #eef2f7;
    border-radius: 0.75rem;
    transition:
        background 0.2s,
        border-color 0.2s;
}
.info-tile:hover {
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.info-tile i {
    font-size: 1rem;
    margin-top: 0.15rem;
    flex-shrink: 0;
}
.info-tile .label {
    font-size: 0.7rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 600;
}
.info-tile .value {
    font-size: 0.9rem;
    color: #334155;
    font-weight: 600;
    margin-top: 0.1rem;
}
</style>
