<script setup>
import {Link} from "@inertiajs/inertia-vue3";

const props = defineProps({
    users: Array,
});

</script>

<template>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.tailwindcss.com" rel="stylesheet">
    </head>
    <body>
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->

        <!-- Main Chat Area -->

        <slot/>
    </div>
    </body>
    </html>

</template>

<style scoped>

</style>
