<template>
    <div class="min-h-screen bg-kubgtu-gray-light flex flex-col">
        <!-- Header -->
        <header class="bg-kubgtu-white border-b border-border-light">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <Link :href="route('guest.home')" class="flex items-center gap-3">
                        <!-- Иконка логотипа -->
                        <img 
                            v-if="logoIcon"
                            :src="logoIcon" 
                            alt="Кластер" 
                            class="h-10 w-10 object-contain"
                            @error="$event.target.style.display = 'none'"
                        />
                        <!-- Длинное лого -->
                        <img 
                            v-else-if="logoImage"
                            :src="logoImage" 
                            alt="Кластер" 
                            class="h-10 object-contain"
                            @error="$event.target.style.display = 'none'"
                        />
                        <!-- Текстовое лого по умолчанию -->
                        <h1 v-else class="text-2xl font-bold text-primary">Кластер</h1>
                    </Link>
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

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-surface">
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-extrabold text-text-primary">
                        {{ title }}
                    </h2>
                </div>
                
                <FlashMessage />
                <div class="bg-kubgtu-white py-8 px-6 shadow-lg rounded-xl border border-border-light">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import { routeExists } from '@/Utils/routes';

defineProps({
    title: {
        type: String,
        default: 'Кластер',
    },
    logoIcon: {
        type: String,
        default: null,
        // Иконка (просто буква) - используется если указано
    },
    logoImage: {
        type: String,
        default: '/images/logo/logo.png',
        // Длинное лого (весь текст) - используется если logoIcon не указан
    },
});

const safeRoute = (routeName) => {
    if (routeExists(routeName)) {
        try {
            return route(routeName);
        } catch (e) {
            return '#';
        }
    }
    return '#';
};
</script>

