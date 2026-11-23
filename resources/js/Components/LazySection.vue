<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    section: {
        type: String,
        required: true,
    },
    rootMargin: {
        type: String,
        default: "300px 0px",
    },
    minHeight: {
        type: String,
        default: null,
    },
});

const sectionElement = ref(null);
const isVisible = ref(false);

let observer = null;

onMounted(() => {
    if (typeof window === "undefined" || !("IntersectionObserver" in window)) {
        isVisible.value = true;
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                isVisible.value = true;
                observer?.disconnect();
                observer = null;
            }
        },
        { rootMargin: props.rootMargin },
    );

    if (sectionElement.value) {
        observer.observe(sectionElement.value);
    }
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <section
        ref="sectionElement"
        :data-lazy-section="section"
        :style="!isVisible && minHeight ? { minHeight } : undefined"
    >
        <slot v-if="isVisible" />

        <div v-else class="lazy-section-placeholder" aria-hidden="true"></div>
    </section>
</template>

<style scoped>
.lazy-section-placeholder {
    width: 100%;
    height: 100%;
    contain: layout paint;
}
</style>
