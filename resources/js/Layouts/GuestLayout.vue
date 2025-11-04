<template>
    <div class="min-h-screen bg-kubgtu-gray-light flex flex-col">
        <!-- Header -->
        <header class="bg-kubgtu-white border-b border-border-light">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <img src="/img/logo.png" alt="Klust" class="h-10" v-if="false" />
                        <h1 class="text-2xl font-bold text-primary">Klust</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-text-primary hover:text-primary transition-colors"
                        >
                            Войти
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-4 py-2 text-sm font-medium text-kubgtu-white bg-primary rounded-lg hover:bg-primary-light transition-colors shadow-sm"
                        >
                            Регистрация
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-surface">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-extrabold text-text-primary">
                        {{ title }}
                    </h2>
                </div>
                
                <div class="bg-kubgtu-white py-8 px-6 shadow-lg rounded-xl border border-border-light">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>

