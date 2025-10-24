<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import Toast from '@/components/Toast.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>

    <Toast
        v-if="flash?.success"
        :message="flash.success"
        type="success"
    />
    <Toast
        v-if="flash?.error"
        :message="flash.error"
        type="error"
    />
</template>
