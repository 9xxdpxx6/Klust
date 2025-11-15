<template>
    <GuestLayout title="Вход в систему">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Flash сообщения -->
            <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-text-primary">
                    Email
                </label>
                <div class="mt-1">
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-border rounded-md shadow-sm placeholder-text-muted focus:outline-none focus:border-primary sm:text-sm"
                        :class="{ 'border-red-500': form.errors.email }"
                        placeholder="Введите email"
                    />
                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                        {{ form.errors.email }}
                    </p>
                </div>
            </div>

            <!-- Пароль -->
            <div>
                <label for="password" class="block text-sm font-medium text-text-primary">
                    Пароль
                </label>
                <div class="mt-1">
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="appearance-none block w-full px-3 py-2 border border-border rounded-md shadow-sm placeholder-text-muted focus:outline-none focus:border-primary sm:text-sm"
                        :class="{ 'border-red-500': form.errors.password }"
                        placeholder="Введите пароль"
                    />
                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>
            </div>

            <!-- Запомнить меня -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input
                        id="remember"
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 text-primary border-border rounded"
                    />
                    <label for="remember" class="ml-2 block text-sm text-text-primary">
                        Запомнить меня
                    </label>
                </div>
            </div>

            <!-- Кнопка входа -->
            <div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-kubgtu-white bg-primary hover:bg-primary-light focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="form.processing">Вход...</span>
                    <span v-else>Войти</span>
                </button>
            </div>

            <!-- Ссылка на регистрацию -->
            <div class="text-center">
                <Link :href="route('register')" class="text-sm text-primary hover:text-primary-light transition-colors">
                    Нет аккаунта? Зарегистрироваться
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

