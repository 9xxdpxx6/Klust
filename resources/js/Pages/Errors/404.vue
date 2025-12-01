<template>
    <div class="bg-kubgtu-white py-12 px-6 shadow-lg rounded-xl border border-border-light text-center">
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full mb-6 shadow-xl">
                <i class="pi pi-search text-6xl text-white"></i>
            </div>
            <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-2">
                Страница не найдена
            </h2>
            <p class="text-gray-600 max-w-md mx-auto mb-8">
                Запрашиваемая страница не существует или была перемещена. Проверьте правильность адреса.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
                v-if="homeRoute"
                :href="homeRoute"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg font-medium"
            >
                <i class="pi pi-home"></i>
                На главную
            </Link>
            <Link
                v-else-if="routeExists('home')"
                :href="route('home')"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg font-medium"
            >
                <i class="pi pi-home"></i>
                На главную
            </Link>
            <button
                @click="$inertia.reload()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all font-medium"
            >
                <i class="pi pi-refresh"></i>
                Обновить
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import ErrorLayout from '@/Layouts/ErrorLayout.vue';
import { routeExists } from '@/Utils/routes';

defineOptions({
    layout: ErrorLayout,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const homeRoute = computed(() => {
    if (!user.value) {
        return routeExists('home') ? route('home') : null;
    }

    const roles = user.value.roles || [];
    
    if (roles.includes('admin') || roles.includes('teacher')) {
        return routeExists('admin.dashboard') ? route('admin.dashboard') : null;
    }
    if (roles.includes('student')) {
        return routeExists('student.dashboard') ? route('student.dashboard') : null;
    }
    if (roles.includes('partner')) {
        return routeExists('partner.dashboard') ? route('partner.dashboard') : null;
    }
    
    return routeExists('home') ? route('home') : null;
});
</script>

