<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
    section: { type: String, required: true },
});

const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                observer.unobserve(entry.target);
            }
        });
    });
    observer.observe(
        document.querySelector(`[data-section="${props.section}"]`),
    );
});
</script>

<template>
    <div :data-section="section">
        <slot v-if="isVisible"></slot>
    </div>
</template>
