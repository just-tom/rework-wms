<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <Toaster rich-colors position="top-center" />
    <main
        class="mx-auto flex h-full min-h-screen w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl"
    >
        <slot />
    </main>
</template>
