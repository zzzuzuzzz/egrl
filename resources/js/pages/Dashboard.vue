<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {Head, useForm} from '@inertiajs/vue3';
import InputError from "@/components/InputError.vue";
import {LoaderCircle} from "lucide-vue-next";
import {Label} from "@/components/ui/label";
import {Input} from "@/components/ui/input";
import {Checkbox} from "@/components/ui/checkbox";
import TextLink from "@/components/TextLink.vue";
import {Button} from "@/components/ui/button";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Главный экран',
        href: '/dashboard',
    },
];

const form = useForm({
    inn: '',
});

const submit = () => {
    form.get(route('makeListOfShareholders'));
};
</script>

<template>
    <Head title="Главный экран" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative max-h-[20vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min p-5">
                <form @submit.prevent="submit" class="flex flex-col gap-6">
                    <div class="flex" style="justify-content: center; flex-direction: column; align-items: center">
                        <Label for="inn" class="mb-2">Введите ИНН компании</Label>
                        <Input
                            id="inn"
                            type="number"
                            v-model="form.inn"
                            placeholder="1234567890"
                            class="w-1/3"
                        />
                        <InputError :message="form.errors.inn" />
                        <Button type="submit" class="mt-2 w-1/3">
                            Войти
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
