<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "@/Components/FrontEnd/Reporter/Layout.vue";
import TextInput from "@/Shared/TextInput.vue";
import Breadcrumb from "@/Shared/Breadcrumb.vue";
</script>

<template>
    <Layout>
        <Head title="Id Card Page" />
        <div>
            <h2 class="text-2xl font-bold">Add Pin Code</h2>
            <Breadcrumb />
        </div>
        <div class="border border-slate-300 shadow-lg bg-white p-2 w-[20%] rounded">
            <TextInput
                id="name"
                label="Pin Code"
                type="text"
                placeholder="Pin Code..."
            />
            <button class="btn rounded my-5" type="submit">Submit</button>
        </div>
    </Layout>
</template>
