<script setup>
import { watch, ref, onBeforeUnmount } from "vue";

const props = defineProps({
    show: { type: Boolean, default: false },
    type: { type: String, default: "info" }, // success | error | warning | info
    title: { type: String, default: "" },
    message: { type: String, default: "" },
    okText: { type: String, default: "ঠিক আছে" },
});

const emit = defineEmits(["close"]);

const visible = ref(props.show);
watch(
    () => props.show,
    (v) => (visible.value = v),
);

function close() {
    visible.value = false;
    emit("close");
}

// ESC to close
function onKey(e) {
    if (e.key === "Escape" && visible.value) close();
}
window.addEventListener("keydown", onKey);
onBeforeUnmount(() => window.removeEventListener("keydown", onKey));

// icon + color map
const theme = {
    success: {
        icon: "fa-solid fa-circle-check",
        ring: "ring-green-500/30",
        bg: "bg-green-100",
        text: "text-green-600",
        btn: "bg-green-600 hover:bg-green-700",
    },
    error: {
        icon: "fa-solid fa-circle-exclamation",
        ring: "ring-red-500/30",
        bg: "bg-red-100",
        text: "text-red-600",
        btn: "bg-red-600 hover:bg-red-700",
    },
    warning: {
        icon: "fa-solid fa-triangle-exclamation",
        ring: "ring-amber-500/30",
        bg: "bg-amber-100",
        text: "text-amber-600",
        btn: "bg-amber-600 hover:bg-amber-700",
    },
    info: {
        icon: "fa-solid fa-circle-info",
        ring: "ring-blue-500/30",
        bg: "bg-blue-100",
        text: "text-blue-600",
        btn: "bg-blue-600 hover:bg-blue-700",
    },
};
const t = (ty) => theme[ty] ?? theme.info;
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="close"
                ></div>

                <!-- Modal card -->
                <Transition
                    appear
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 scale-90 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                >
                    <div
                        v-if="visible"
                        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                        role="dialog"
                        aria-modal="true"
                    >
                        <!-- Accent bar -->
                        <div
                            class="h-1.5 w-full"
                            :class="{
                                'bg-green-500': type === 'success',
                                'bg-red-500': type === 'error',
                                'bg-amber-500': type === 'warning',
                                'bg-blue-500': type === 'info',
                            }"
                        ></div>

                        <div class="p-6 sm:p-8 text-center">
                            <!-- Icon -->
                            <div
                                class="mx-auto w-16 h-16 rounded-full flex items-center justify-center mb-5 ring-4"
                                :class="[
                                    t(type).bg,
                                    t(type).text,
                                    t(type).ring,
                                ]"
                            >
                                <i :class="t(type).icon" class="text-3xl"></i>
                            </div>

                            <!-- Title -->
                            <h3
                                v-if="title"
                                class="text-lg sm:text-xl font-bold text-slate-800 mb-2"
                            >
                                {{ title }}
                            </h3>

                            <!-- Message -->
                            <p
                                class="text-sm sm:text-base text-slate-600 leading-relaxed"
                            >
                                {{ message }}
                            </p>

                            <!-- OK button -->
                            <button
                                type="button"
                                class="mt-6 w-full text-white font-semibold py-2.5 rounded-lg shadow-md transition active:scale-[0.98]"
                                :class="t(type).btn"
                                @click="close"
                            >
                                {{ okText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
