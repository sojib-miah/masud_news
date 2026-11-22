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
        default: "250px",
    },
});

const sectionElement = ref(null);
const isVisible = ref(false);

let observer = null;

onMounted(() => {
    if (!("IntersectionObserver" in window)) {
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
        {
            rootMargin: props.rootMargin,
        },
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
        :style="{
            minHeight: isVisible ? undefined : minHeight,
        }"
    >
        <slot v-if="isVisible" />

        <div v-else class="lazy-section-placeholder" aria-hidden="true"></div>
    </section>
</template>

<style scoped>
.lazy-section-placeholder {
    width: 100%;
    min-height: inherit;
    contain: layout paint;
}
</style>
