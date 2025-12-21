<template>
    <Head title="Подтверждение email" />
    <div class="space-y-6">
        <!-- Flash сообщения -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ $page.props.flash.error }}
        </div>

        <!-- Основное сообщение -->
        <div class="text-center space-y-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-text-primary">
                Подтвердите ваш email адрес
            </h2>
            <p class="text-text-secondary">
                Перед тем как продолжить, пожалуйста, проверьте вашу почту для ссылки подтверждения.
            </p>
            <p v-if="$page.props.auth?.user" class="text-sm text-text-muted">
                Письмо было отправлено на адрес: <strong>{{ $page.props.auth.user.email }}</strong>
            </p>
        </div>

        <!-- Инструкции -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
            <p class="text-sm font-medium text-blue-900">
                Если вы не получили письмо:
            </p>
            <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                <li>Проверьте папку "Спам" или "Нежелательная почта"</li>
                <li>Убедитесь, что email адрес указан правильно</li>
                <li>Подождите несколько минут, письмо может идти с задержкой</li>
            </ul>
        </div>

        <!-- Кнопка повторной отправки -->
        <form @submit.prevent="submit">
            <button
                type="submit"
                :disabled="processing"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-kubgtu-white bg-primary hover:bg-primary-light focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <span v-if="processing">Отправка...</span>
                <span v-else>Отправить письмо повторно</span>
            </button>
        </form>

        <!-- Выход -->
        <div class="text-center">
            <form @submit.prevent="logout" class="inline">
                <button
                    type="submit"
                    class="text-sm text-text-muted hover:text-text-primary transition-colors"
                >
                    Выйти из аккаунта
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed, onMounted } from 'vue';

const page = usePage();
const processing = ref(false);

// Проверяем, подтвержден ли email
const isVerified = computed(() => {
    return page.props.auth?.user?.email_verified_at !== null;
});

// Если email уже подтвержден, редиректим на dashboard
onMounted(() => {
    if (isVerified.value) {
        router.visit(route('dashboard'), {
            only: [],
            preserveState: false,
        });
    }
});

const submit = () => {
    processing.value = true;
    router.post(route('verification.send'), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

