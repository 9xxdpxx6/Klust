<template>
    <div class="bg-kubgtu-white py-12 px-6 shadow-lg rounded-xl border border-border-light text-center">
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-amber-500 to-amber-600 rounded-full mb-6 shadow-xl">
                <i class="pi pi-user text-6xl text-white"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-900 mb-4">401</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">
                Требуется авторизация
            </h2>
            <p class="text-gray-600 max-w-md mx-auto mb-8">
                Для доступа к этому ресурсу необходимо войти в систему. Пожалуйста, выполните вход.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
                :href="route('login')"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg font-medium"
            >
                <i class="pi pi-sign-in"></i>
                Войти
            </Link>
            <Link
                v-if="routeExists('home')"
                :href="route('home')"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all font-medium"
            >
                <i class="pi pi-home"></i>
                На главную
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ErrorLayout from '@/Layouts/ErrorLayout.vue';
import { routeExists } from '@/Utils/routes';
import { route } from 'ziggy-js';

defineOptions({
    layout: ErrorLayout,
});
</script>
