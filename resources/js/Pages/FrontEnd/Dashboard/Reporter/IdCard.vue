<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import { ref, computed } from "vue";

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});

// Pin state
const pin = ref("");
const error = ref("");
const verified = ref(false);

// The correct PIN — change here if needed
const CORRECT_PIN = "1234";

// Fake-but-plausible data shown on the card (replace with real props if you have them)
const cardData = computed(() => ({
    name: user.value.name || "Reporter Name",
    designation: "Staff Reporter",
    id: user.value.id
        ? `RPT-${String(user.value.id).padStart(4, "0")}`
        : "RPT-0000",
    phone: user.value.phone || "+880 1XXX-XXXXXX",
    email: user.value.email || "reporter@example.com",
    bloodGroup: user.value.blood_group || "O+",
    issued: new Date().toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }),
}));

function submitPin() {
    error.value = "";
    if (pin.value.trim() === "") {
        error.value = "Please enter the PIN code.";
        return;
    }
    if (pin.value.trim() === CORRECT_PIN) {
        verified.value = true;
    } else {
        verified.value = false;
        error.value = "Incorrect PIN code. Please try again.";
    }
}

function resetPin() {
    pin.value = "";
    error.value = "";
    verified.value = false;
}
</script>

<template>
    <Layout>
        <Head title="Id Card Page" />

        <div class="min-h-screen bg-slate-50">
            <div>
                <!-- ==================== HEADER ==================== -->
                <div
                    class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-xl mb-6 overflow-hidden"
                >
                    <div
                        class="px-5 sm:px-8 py-5 sm:py-6 flex items-center gap-3"
                    >
                        <div
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center text-white text-xl"
                        >
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <h1
                                class="text-white text-lg sm:text-2xl font-bold leading-tight"
                            >
                                Reporter ID Card
                            </h1>
                            <p class="text-green-100 text-xs sm:text-sm mt-0.5">
                                Enter your PIN code to view your ID card
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ==================== MAIN GRID ==================== -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                    <!-- ============ LEFT: PIN FORM ============ -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6"
                    >
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-lock text-green-600"></i>
                            <h2 class="text-lg font-bold text-slate-800">
                                Enter PIN Code
                            </h2>
                        </div>

                        <p class="text-sm text-slate-500 mb-4">
                            Please enter the 4-digit PIN code provided to you to
                            access your ID card.
                        </p>

                        <form @submit.prevent="submitPin" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-slate-700 mb-1"
                                >
                                    Pin Code
                                </label>
                                <TextInput
                                    v-model="pin"
                                    id="pin"
                                    type="text"
                                    inputmode="numeric"
                                    maxlength="4"
                                    placeholder="Enter 4-digit PIN"
                                    :error="error"
                                />
                            </div>

                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-2.5 rounded-lg shadow transition flex items-center justify-center gap-2"
                                >
                                    <i class="fa-solid fa-unlock"></i>
                                    Submit
                                </button>

                                <button
                                    v-if="verified"
                                    type="button"
                                    @click="resetPin"
                                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-4 rounded-lg transition"
                                >
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>
                            </div>
                        </form>

                        <div
                            v-if="verified"
                            class="mt-4 flex items-center gap-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2"
                        >
                            <i class="fa-solid fa-circle-check"></i>
                            PIN verified successfully — your ID card is shown on
                            the right.
                        </div>

                        <div
                            v-else
                            class="mt-4 text-xs text-slate-400 flex items-start gap-2"
                        >
                            <i class="fa-solid fa-circle-info mt-0.5"></i>
                            <span>
                                Tip: The demo PIN is
                                <strong class="text-slate-600">1234</strong>.
                                Replace with your real logic if needed.
                            </span>
                        </div>
                    </div>

                    <!-- ============ RIGHT: ID CARD ============ -->
                    <div
                        class="transition-all duration-500"
                        :class="
                            verified
                                ? 'opacity-100 translate-y-0'
                                : 'opacity-0 translate-y-4 pointer-events-none lg:opacity-100 lg:translate-y-0 lg:pointer-events-auto lg:blur-md lg:select-none'
                        "
                    >
                        <!-- Placeholder when not verified (desktop) -->
                        <div
                            v-if="!verified"
                            class="hidden lg:flex bg-white rounded-2xl border-2 border-dashed border-slate-300 p-10 flex-col items-center justify-center text-center min-h-[420px]"
                        >
                            <i
                                class="fa-solid fa-id-card text-5xl text-slate-300 mb-3"
                            ></i>
                            <p class="text-slate-500 font-semibold">
                                ID card is locked
                            </p>
                            <p class="text-sm text-slate-400 mt-1">
                                Enter the correct PIN on the left to reveal your
                                ID card.
                            </p>
                        </div>

                        <!-- The actual ID card -->
                        <div v-if="verified" class="id-card-wrap">
                            <!-- Front side -->
                            <div class="id-card">
                                <!-- top strip -->
                                <div class="id-card-topbar">
                                    <div class="flex items-center gap-2">
                                        <div class="id-card-logo">
                                            <i
                                                class="fa-solid fa-newspaper"
                                            ></i>
                                        </div>
                                        <div>
                                            <div
                                                class="text-white font-bold text-sm leading-tight"
                                            >
                                                Press Club
                                            </div>
                                            <div
                                                class="text-green-100 text-[10px] leading-tight"
                                            >
                                                Reporter ID Card
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="text-white text-[10px] bg-white/15 px-2 py-0.5 rounded"
                                    >
                                        {{ cardData.issued }}
                                    </div>
                                </div>

                                <!-- body -->
                                <div class="id-card-body">
                                    <div class="id-card-photo">
                                        <div
                                            class="w-full h-full flex items-center justify-center bg-slate-100 text-slate-400"
                                        >
                                            <i
                                                class="fa-solid fa-user text-3xl"
                                            ></i>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h3
                                            class="text-slate-900 font-bold text-lg leading-tight truncate"
                                        >
                                            {{ cardData.name }}
                                        </h3>
                                        <p
                                            class="text-green-700 text-xs font-semibold mb-3"
                                        >
                                            {{ cardData.designation }}
                                        </p>

                                        <div class="space-y-1.5">
                                            <div class="id-card-row">
                                                <span class="id-card-label">
                                                    <i
                                                        class="fa-solid fa-hashtag w-3"
                                                    ></i>
                                                    ID
                                                </span>
                                                <span class="id-card-value">
                                                    {{ cardData.id }}
                                                </span>
                                            </div>
                                            <div class="id-card-row">
                                                <span class="id-card-label">
                                                    <i
                                                        class="fa-solid fa-phone w-3"
                                                    ></i>
                                                    Phone
                                                </span>
                                                <span class="id-card-value">
                                                    {{ cardData.phone }}
                                                </span>
                                            </div>
                                            <div class="id-card-row">
                                                <span class="id-card-label">
                                                    <i
                                                        class="fa-solid fa-envelope w-3"
                                                    ></i>
                                                    Email
                                                </span>
                                                <span
                                                    class="id-card-value truncate"
                                                >
                                                    {{ cardData.email }}
                                                </span>
                                            </div>
                                            <div class="id-card-row">
                                                <span class="id-card-label">
                                                    <i
                                                        class="fa-solid fa-droplet w-3"
                                                    ></i>
                                                    Blood
                                                </span>
                                                <span class="id-card-value">
                                                    {{ cardData.bloodGroup }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- footer -->
                                <div class="id-card-footer">
                                    <div class="flex items-center gap-1">
                                        <i
                                            class="fa-solid fa-shield-halved text-green-700 text-[10px]"
                                        ></i>
                                        <span
                                            class="text-[10px] text-slate-500 font-semibold"
                                        >
                                            Authorized Reporter
                                        </span>
                                    </div>
                                    <div
                                        class="text-[10px] text-slate-400 font-mono"
                                    >
                                        {{ cardData.id }}
                                    </div>
                                </div>

                                <!-- watermark -->
                                <div class="id-card-watermark">
                                    <i class="fa-solid fa-newspaper"></i>
                                </div>
                            </div>

                            <!-- Back side / barcode strip -->
                            <div class="id-card-back">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p
                                            class="text-[10px] text-slate-400 uppercase tracking-wider"
                                        >
                                            This card is property of
                                        </p>
                                        <p
                                            class="text-slate-700 font-bold text-xs"
                                        >
                                            Press Club — Bangladesh
                                        </p>
                                    </div>
                                    <i
                                        class="fa-solid fa-qrcode text-2xl text-slate-700"
                                    ></i>
                                </div>
                                <p
                                    class="text-[9px] text-slate-400 mt-2 leading-relaxed"
                                >
                                    If found, please return to the nearest Press
                                    Club office. This card is non-transferable.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
/* ---------- ID CARD CONTAINER ---------- */
.id-card-wrap {
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: cardIn 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(16px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ---------- FRONT CARD ---------- */
.id-card {
    position: relative;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow:
        0 10px 30px -8px rgba(0, 147, 12, 0.25),
        0 4px 12px -4px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
}

.id-card-topbar {
    background: linear-gradient(135deg, #00930c, #00b515);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.id-card-logo {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    flex-shrink: 0;
}

.id-card-body {
    display: flex;
    gap: 14px;
    padding: 16px;
    position: relative;
    z-index: 1;
}

.id-card-photo {
    width: 92px;
    height: 112px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #d1fae5;
    flex-shrink: 0;
    box-shadow: 0 4px 10px -4px rgba(0, 0, 0, 0.15);
}

.id-card-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
}

.id-card-label {
    color: #64748b;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    min-width: 54px;
    flex-shrink: 0;
}

.id-card-value {
    color: #1e293b;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.id-card-footer {
    padding: 10px 16px;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: #f8fafc;
}

.id-card-watermark {
    position: absolute;
    right: -14px;
    bottom: 44px;
    font-size: 90px;
    color: #00930c;
    opacity: 0.05;
    pointer-events: none;
    line-height: 1;
    transform: rotate(-12deg);
    z-index: 0;
}

/* ---------- BACK STRIP ---------- */
.id-card-back {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 0 4px 12px -6px rgba(0, 0, 0, 0.08);
}

/* ---------- RESPONSIVE TUNING ---------- */
@media (max-width: 640px) {
    .id-card-photo {
        width: 76px;
        height: 94px;
    }
    .id-card-row {
        font-size: 11px;
    }
    .id-card-label {
        min-width: 48px;
    }
}
</style>
