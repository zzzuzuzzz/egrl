<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Настройки внешнего вида',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Настройки внешнего вида" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Настройки внешнего вида" description="Внешний вид приложения" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
