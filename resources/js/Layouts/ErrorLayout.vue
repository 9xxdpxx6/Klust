<template>
    <div class="min-h-screen bg-kubgtu-gray-light flex flex-col">
        <!-- Header -->
        <header class="bg-kubgtu-white border-b border-border-light">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <Link 
                            :href="homeRoute"
                            class="flex items-center gap-3"
                        >
                            <!-- Длинное лого (приоритет) -->
                            <img 
                                v-if="logoImage && !logoError"
                                :src="logoImage" 
                                alt="Кластер" 
                                class="h-10 object-contain"
                                @error="handleLogoError"
                            />
                            <!-- Иконка логотипа (fallback) -->
                            <img 
                                v-else-if="logoIcon && !logoError"
                                :src="logoIcon" 
                                alt="Кластер" 
                                class="h-10 w-10 object-contain"
                                @error="handleLogoError"
                            />
                            <!-- Текстовое лого по умолчанию -->
                            <h1 v-else class="text-2xl font-bold text-primary">Кластер</h1>
                        </Link>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            v-if="routeExists('login')"
                            :href="safeRoute('login')"
                            class="text-sm font-medium text-text-primary hover:text-primary transition-colors"
                        >
                            Войти
                        </Link>
                        <Link
                            v-if="routeExists('register')"
                            :href="safeRoute('register')"
                            class="px-4 py-2 text-sm font-medium text-kubgtu-white bg-primary rounded-lg hover:bg-primary-light transition-colors shadow-sm"
                        >
                            Регистрация
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content - без дополнительного заголовка -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-surface">
            <div class="w-full max-w-2xl">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { routeExists } from '@/Utils/routes';

const props = defineProps({
    logoIcon: {
        type: String,
        default: null,
    },
    logoImage: {
        type: String,
        default: '/images/logo/logo.png',
    },
});

const logoError = ref(false);

const handleLogoError = () => {
    logoError.value = true;
};

const safeRoute = (routeName) => {
    if (routeExists(routeName)) {
        try {
            return route(routeName);
        } catch (e) {
            return '/';
        }
    }
    return '/';
};

const homeRoute = safeRoute('guest.home');
</script>

