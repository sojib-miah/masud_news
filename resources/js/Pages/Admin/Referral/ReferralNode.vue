<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

defineOptions({
    name: "ReferralNode",
});

const props = defineProps({
    user: Object,
    depth: { type: Number, default: 0 },
});

// Collapse/expand children
const open = ref(true);

const hasChildren = computed(
    () => props.user?.referrals && props.user.referrals.length > 0,
);

const childCount = computed(() => props.user?.referrals?.length ?? 0);

// Total descendants (recursive count) — computed once
function countDescendants(node) {
    if (!node?.referrals?.length) return 0;
    return node.referrals.reduce(
        (sum, child) => sum + 1 + countDescendants(child),
        0,
    );
}

const totalDescendants = computed(() => countDescendants(props.user));

// Depth-based accent color (cycle through a palette)
const depthPalette = [
    {
        ring: "ring-violet-300",
        text: "text-violet-700",
        bg: "bg-violet-50",
        bar: "border-violet-300",
        badge: "from-violet-500 to-violet-600",
    },
    {
        ring: "ring-blue-300",
        text: "text-blue-700",
        bg: "bg-blue-50",
        bar: "border-blue-300",
        badge: "from-blue-500 to-blue-600",
    },
    {
        ring: "ring-emerald-300",
        text: "text-emerald-700",
        bg: "bg-emerald-50",
        bar: "border-emerald-300",
        badge: "from-emerald-500 to-emerald-600",
    },
    {
        ring: "ring-amber-300",
        text: "text-amber-700",
        bg: "bg-amber-50",
        bar: "border-amber-300",
        badge: "from-amber-500 to-amber-600",
    },
    {
        ring: "ring-rose-300",
        text: "text-rose-700",
        bg: "bg-rose-50",
        bar: "border-rose-300",
        badge: "from-rose-500 to-rose-600",
    },
    {
        ring: "ring-cyan-300",
        text: "text-cyan-700",
        bg: "bg-cyan-50",
        bar: "border-cyan-300",
        badge: "from-cyan-500 to-cyan-600",
    },
];

const palette = computed(() => depthPalette[props.depth % depthPalette.length]);

function initials(name) {
    if (!name) return "?";
    return name
        .split(" ")
        .slice(0, 2)
        .map((w) => w[0])
        .join("")
        .toUpperCase();
}

function formatMoney(n) {
    if (n === null || n === undefined || n === "") return "0";
    return Number(n).toLocaleString("en-US");
}
</script>

<template>
    <div class="relative">
        <!-- Connector line (left) — hidden on root, shown on children -->
        <div
            v-if="depth > 0"
            class="absolute left-0 top-0 bottom-0 w-px bg-slate-200"
            aria-hidden="true"
        ></div>

        <div :class="depth > 0 ? 'pl-4 sm:pl-6' : ''">
            <!-- USER CARD -->
            <div
                class="group relative rounded-xl bg-white ring-1 shadow-sm hover:shadow-md transition-all"
                :class="palette.ring"
            >
                <!-- Left accent bar -->
                <div
                    class="absolute left-0 top-2 bottom-2 w-1 rounded-full"
                    :class="['bg-gradient-to-b', palette.badge]"
                ></div>

                <div class="flex items-center gap-3 p-3 sm:p-4 pl-4 sm:pl-5">
                    <!-- Avatar -->
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white font-bold text-xs shadow"
                        :class="['bg-gradient-to-br', palette.badge]"
                    >
                        {{ initials(user.name) }}
                    </div>

                    <!-- Name + contact -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p
                                class="text-sm font-bold text-slate-800 line-clamp-1"
                            >
                                {{ user.name }}
                                <small class="text-[10px] text-slate-500"
                                    >({{ user.role }})</small
                                >
                            </p>
                            <!-- Depth badge -->
                            <span
                                v-if="depth > 0"
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1"
                                :class="[
                                    palette.bg,
                                    palette.text,
                                    palette.ring,
                                ]"
                            >
                                L{{ depth }}
                            </span>
                            <!-- Referred by label -->
                            <span
                                v-if="depth > 0"
                                class="text-[10px] font-semibold text-slate-400"
                            >
                                <i class="fa-solid fa-angle-down mr-0.5"></i>
                                referred
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 line-clamp-1 mt-0.5">
                            <i class="fa-solid fa-phone text-[10px] mr-1"></i>
                            {{ user.phone || "—" }}
                        </p>
                    </div>

                    <!-- Earning -->
                    <div class="text-right shrink-0">
                        <p
                            class="text-[9px] uppercase tracking-wider text-slate-500 font-bold"
                        >
                            Earning
                        </p>
                        <p
                            class="text-sm sm:text-base font-extrabold text-emerald-600 whitespace-nowrap"
                        >
                            ৳{{ formatMoney(user.total_earning) }}
                        </p>
                        <p class="text-[9px] text-slate-400 font-semibold">
                            ৳{{ formatMoney(user.total_withdrawn) }} withdrawn
                        </p>
                    </div>

                    <!-- Expand/collapse toggle -->
                    <button
                        v-if="hasChildren"
                        @click="open = !open"
                        class="shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100 transition"
                        :title="open ? 'Collapse' : 'Expand'"
                    >
                        <i
                            class="fa-solid text-[10px] transition-transform"
                            :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"
                        ></i>
                    </button>
                </div>

                <!-- Footer strip: child count -->
                <div
                    v-if="hasChildren"
                    class="flex items-center gap-2 border-t border-slate-100 px-4 sm:px-5 py-1.5 bg-slate-50/50"
                >
                    <i
                        class="fa-solid fa-users text-[10px]"
                        :class="palette.text"
                    ></i>
                    <p class="text-[11px] font-semibold text-slate-600">
                        {{ childCount }} direct
                        <span class="text-slate-400">•</span>
                        {{ totalDescendants }} total in network
                    </p>
                </div>
            </div>

            <!-- CHILD REFERRALS -->
            <div v-if="hasChildren && open" class="mt-2 space-y-2 relative">
                <ReferralNode
                    v-for="child in user.referrals"
                    :key="child.id"
                    :user="child"
                    :depth="depth + 1"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (prefers-reduced-motion: reduce) {
    * {
        transition: none !important;
        animation: none !important;
    }
}
</style>
