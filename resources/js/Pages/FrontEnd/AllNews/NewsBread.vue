<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { route } from "ziggy-js";

const page = usePage();

const category = computed(() => page.props.category ?? null);
const subcategory = computed(() => page.props.subcategory ?? null);
</script>

<template>
    <nav class="breadcrumb-wrap" aria-label="Breadcrumb">
        <div class="container mx-auto px-3 sm:px-4 lg:px-6">
            <ol class="breadcrumb">
                <!-- Home -->
                <li class="crumb">
                    <Link
                        :href="route('home')"
                        class="crumb-link home"
                        aria-label="Home"
                    >
                        <i class="fa-solid fa-house"></i>
                        <span class="home-label">হোম</span>
                    </Link>
                </li>

                <li class="crumb-sep" aria-hidden="true">
                    <i class="fa-solid fa-angle-right"></i>
                </li>

                <!-- Category -->
                <li v-if="category" class="crumb">
                    <Link
                        :href="route('all.news', category.slug)"
                        class="crumb-link"
                    >
                        {{ category.name }}
                    </Link>
                </li>

                <!-- Subcategory -->
                <template v-if="subcategory">
                    <li class="crumb-sep" aria-hidden="true">
                        <i class="fa-solid fa-angle-right"></i>
                    </li>
                    <li class="crumb">
                        <span class="crumb-current" aria-current="page">
                            {{ subcategory.name }}
                        </span>
                    </li>
                </template>
            </ol>
        </div>
    </nav>
</template>

<style scoped>
/* ==================== WRAPPER ==================== */
.breadcrumb-wrap {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 10px 0;
}

/* ==================== LIST ==================== */
.breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 0.85rem;
    font-family: "Hind Siliguri", "Roboto", sans-serif;
}

/* ==================== CRUMB ==================== */
.crumb {
    display: inline-flex;
    align-items: center;
    min-width: 0;
    max-width: 100%;
}

/* ==================== LINK ==================== */
.crumb-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: #ffffff;
    color: #334155;
    font-weight: 500;
    font-size: 0.82rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease,
        transform 0.15s ease,
        box-shadow 0.2s ease;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.crumb-link:hover {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    border-color: #00930c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 147, 12, 0.2);
}

/* Home variant */
.crumb-link.home {
    padding: 5px 12px;
    color: #00930c;
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.crumb-link.home:hover {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    border-color: #00930c;
}

.crumb-link.home i {
    font-size: 0.78rem;
}

.home-label {
    display: inline;
}

/* ==================== SEPARATOR ==================== */
.crumb-sep {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 0.65rem;
    flex-shrink: 0;
    user-select: none;
    padding: 0 2px;
}

/* ==================== CURRENT (page) ==================== */
.crumb-current {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #ffffff;
    font-weight: 600;
    font-size: 0.82rem;
    border-radius: 8px;
    border: 1px solid #00930c;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(0, 147, 12, 0.28);
}

/* ==================== RESPONSIVE ==================== */
/* Tablet and up: slightly larger sizes */
@media (min-width: 640px) {
    .breadcrumb-wrap {
        padding: 12px 0;
    }

    .breadcrumb {
        font-size: 0.9rem;
        gap: 10px;
    }

    .crumb-link,
    .crumb-current {
        font-size: 0.86rem;
        padding: 6px 14px;
    }

    .crumb-sep {
        font-size: 0.7rem;
    }
}

/* Desktop: roomier */
@media (min-width: 1024px) {
    .crumb-link,
    .crumb-current {
        font-size: 0.9rem;
        padding: 7px 16px;
    }
}

/* Small phones: hide "হোম" label, show icon only */
@media (max-width: 480px) {
    .breadcrumb-wrap {
        padding: 8px 0;
    }

    .breadcrumb {
        gap: 6px;
        font-size: 0.78rem;
    }

    .home-label {
        display: none;
    }

    .crumb-link.home {
        padding: 6px 10px;
    }

    .crumb-link,
    .crumb-current {
        font-size: 0.76rem;
        padding: 5px 10px;
    }

    .crumb-sep {
        font-size: 0.6rem;
        padding: 0;
    }
}

/* Very small phones: truncate long category names gracefully */
@media (max-width: 360px) {
    .crumb-link,
    .crumb-current {
        font-size: 0.72rem;
        padding: 4px 8px;
        max-width: 130px;
    }
}

/* ==================== REDUCED MOTION ==================== */
@media (prefers-reduced-motion: reduce) {
    .crumb-link {
        transition: none;
    }
    .crumb-link:hover {
        transform: none;
    }
}
</style>
