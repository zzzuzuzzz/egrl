<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Добро пожаловать">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8" style="background-image: url('/images/bg.jpg')">
        <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">
                <div class="border p-2 bg-white rounded-lg">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Панель управления
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                        >
                            Авторизация
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                        >
                            Регистрация
                        </Link>
                    </template>
                </div>
            </nav>
        </header>
        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] lg:rounded-br-none lg:rounded-tl-lg lg:p-20"
                    style="
                        display: flex;
                        flex-direction: column;
                        justify-content: space-around;
                    "
                >
                    <div>
                        <h1 class="mb-1" style="font-size: 20px; font-family: 'ARCO', sans-serif;">
                            <span style="color: red;">С</span>
                            <span style="color: green;">Д</span>
                            <span style="color: blue;">Е</span>
                            <span style="color: orange;">Л</span>
                            <span style="color: purple;">А</span>
                            <span style="color: brown;">Т</span>
                            <span style="color: gray;">Ь </span>
                            <span style="color: teal;">С</span>
                            <span style="color: violet;">П</span>
                            <span style="color: darkgoldenrod;">И</span>
                            <span style="color: red;">С</span>
                            <span style="color: green;">О</span>
                            <span style="color: blue;">К </span>
                            <span style="color: orange;">У</span>
                            <span style="color: purple;">Ч</span>
                            <span style="color: brown;">А</span>
                            <span style="color: gray;">С</span>
                            <span style="color: teal;">Т</span>
                            <span style="color: violet;">Н</span>
                            <span style="color: darkgoldenrod;">И</span>
                            <span style="color: red;">К</span>
                            <span style="color: green;">О</span>
                            <span style="color: blue;">В</span>
                        </h1>
                        <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                            Программа специально для кишек
                        </p>
                    </div>
                    <ul class="flex gap-3 text-sm leading-normal">
                        <Link
                            :href="route('dashboard')"
                            target="_blank"
                            class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                        >
                            Сделать выписку по ИНН
                        </Link>
                    </ul>
                </div>
                <div
                    class="relative -mb-px aspect-[335/376] w-full shrink-0 overflow-hidden rounded-t-lg bg-[#fff2f2] dark:bg-[#1D0002] lg:-ml-px lg:mb-0 lg:aspect-auto lg:w-[438px] lg:rounded-r-lg lg:rounded-t-none flex"
                    style="align-items: center; justify-content: center;"
                >
                    <img src="/images/cat.gif" alt="я джифка" class="w-3/4">
                </div>
            </main>
        </div>
    </div>
</template>
