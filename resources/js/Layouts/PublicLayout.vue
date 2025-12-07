<template>
    <div class="min-h-screen bg-kubgtu-gray-light flex flex-col">
        <!-- Header -->
        <header class="bg-kubgtu-white border-b border-border-light shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <Link :href="route('guest.home')" class="flex items-center gap-3">
                            <img
                                v-if="logoIcon"
                                :src="logoIcon"
                                alt="Кластер"
                                class="h-10 w-10 object-contain"
                                @error="$event.target.style.display = 'none'"
                            />
                            <img
                                v-else-if="logoImage"
                                :src="logoImage"
                                alt="Кластер"
                                class="h-10 object-contain"
                                @error="$event.target.style.display = 'none'"
                            />
                            <h1 v-else class="text-2xl font-bold text-primary">Кластер</h1>
                        </Link>
                    </div>

                    <!-- Navigation -->
                    <nav class="hidden md:flex items-center gap-6">
                        <Link
                            :href="route('guest.home')"
                            :class="[
                                'text-sm font-medium transition-colors',
                                isCurrentRoute('guest.home')
                                    ? 'text-primary'
                                    : 'text-text-primary hover:text-primary'
                            ]"
                        >
                            Главная
                        </Link>
                        <Link
                            :href="route('guest.about')"
                            :class="[
                                'text-sm font-medium transition-colors',
                                isCurrentRoute('guest.about')
                                    ? 'text-primary'
                                    : 'text-text-primary hover:text-primary'
                            ]"
                        >
                            О проекте
                        </Link>
                        <Link
                            :href="route('guest.cases')"
                            :class="[
                                'text-sm font-medium transition-colors',
                                isCurrentRoute('guest.cases')
                                    ? 'text-primary'
                                    : 'text-text-primary hover:text-primary'
                            ]"
                        >
                            Кейсы
                        </Link>
                    </nav>

                    <!-- Auth Links -->
                    <div class="flex items-center gap-4">
                        <!-- Авторизован -->
                        <template v-if="isAuthenticated">
                            <Link
                                :href="dashboardRoute"
                                class="px-4 py-2 text-sm font-medium text-kubgtu-white bg-primary rounded-lg hover:bg-primary-light transition-colors shadow-sm"
                            >
                                Личный кабинет
                            </Link>
                        </template>

                        <!-- Не авторизован -->
                        <template v-else>
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
                        </template>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden p-2 rounded-md text-text-primary hover:text-primary hover:bg-surface transition-colors"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Menu -->
                <div v-if="mobileMenuOpen" class="md:hidden py-4 border-t border-border-light">
                    <nav class="flex flex-col gap-4">
                        <Link
                            :href="route('guest.home')"
                            :class="[
                                'text-sm font-medium transition-colors',
                                isCurrentRoute('guest.home')
                                    ? 'text-primary'
                                    : 'text-text-primary hover:text-primary'
                            ]"
                            @click="mobileMenuOpen = false"
                        >
                            Главная
                        </Link>
                        <Link
                            :href="route('guest.about')"
                            :class="[
                                'text-sm font-medium transition-colors',
                                isCurrentRoute('guest.about')
                                    ? 'text-primary'
                                    : 'text-text-primary hover:text-primary'
                            ]"
                            @click="mobileMenuOpen = false"
                        >
                            О проекте
                        </Link>
                        <Link
                            :href="route('guest.cases')"
                            :class="[
                                'text-sm font-medium transition-colors',
                                isCurrentRoute('guest.cases')
                                    ? 'text-primary'
                                    : 'text-text-primary hover:text-primary'
                            ]"
                            @click="mobileMenuOpen = false"
                        >
                            Кейсы
                        </Link>

                        <!-- Auth Links Mobile -->
                        <div class="pt-4 border-t border-border-light">
                            <template v-if="isAuthenticated">
                                <Link
                                    :href="dashboardRoute"
                                    class="block px-4 py-2 text-sm font-medium text-center text-kubgtu-white bg-primary rounded-lg hover:bg-primary-light transition-colors"
                                    @click="mobileMenuOpen = false"
                                >
                                    Личный кабинет
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="block text-sm font-medium text-text-primary hover:text-primary transition-colors mb-2"
                                    @click="mobileMenuOpen = false"
                                >
                                    Войти
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="block px-4 py-2 text-sm font-medium text-center text-kubgtu-white bg-primary rounded-lg hover:bg-primary-light transition-colors"
                                    @click="mobileMenuOpen = false"
                                >
                                    Регистрация
                                </Link>
                            </template>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <FlashMessage />
            </div>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-kubgtu-white border-t border-border-light mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- About -->
                    <div>
                        <h3 class="text-sm font-semibold text-text-primary mb-3">О проекте</h3>
                        <p class="text-sm text-text-secondary">
                            Кластер — платформа для case-based обучения, объединяющая студентов и партнеров для работы над реальными проектами.
                        </p>
                    </div>

                    <!-- Navigation -->
                    <div>
                        <h3 class="text-sm font-semibold text-text-primary mb-3">Навигация</h3>
                        <ul class="space-y-2">
                            <li>
                                <Link :href="route('guest.home')" class="text-sm text-text-secondary hover:text-primary transition-colors">
                                    Главная
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('guest.about')" class="text-sm text-text-secondary hover:text-primary transition-colors">
                                    О проекте
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('guest.cases')" class="text-sm text-text-secondary hover:text-primary transition-colors">
                                    Кейсы
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Auth -->
                    <div>
                        <h3 class="text-sm font-semibold text-text-primary mb-3">
                            {{ isAuthenticated ? 'Аккаунт' : 'Начать работу' }}
                        </h3>
                        <ul class="space-y-2">
                            <li v-if="isAuthenticated">
                                <Link :href="dashboardRoute" class="text-sm text-text-secondary hover:text-primary transition-colors">
                                    Личный кабинет
                                </Link>
                            </li>
                            <template v-else>
                                <li>
                                    <Link :href="route('login')" class="text-sm text-text-secondary hover:text-primary transition-colors">
                                        Войти
                                    </Link>
                                </li>
                                <li>
                                    <Link :href="route('register')" class="text-sm text-text-secondary hover:text-primary transition-colors">
                                        Регистрация
                                    </Link>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <!-- University Logo -->
                    <div>
                        <h3 class="text-sm font-semibold text-text-primary mb-3">Партнер</h3>
                        <div class="flex flex-col gap-2 items-start">
                            <img
                                src="/images/logo/kubstu-logo.png"
                                alt="Кубанский государственный технологический университет"
                                class="h-10 w-auto object-contain"
                                @error="$event.target.style.display = 'none'"
                            />
                            <a
                                href="https://kubstu.ru"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm text-text-secondary hover:text-primary transition-colors"
                            >
                                Официальный сайт
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-border-light text-center">
                    <p class="text-sm text-text-secondary">
                        © {{ currentYear }} Кластер. Все права защищены.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import FlashMessage from '@/Components/Shared/FlashMessage.vue'

defineProps({
    logoIcon: {
        type: String,
        default: null,
    },
    logoImage: {
        type: String,
        default: '/images/logo/logo.png',
    },
})

const page = usePage()
const mobileMenuOpen = ref(false)

const currentYear = computed(() => new Date().getFullYear())

const isCurrentRoute = (routeName) => {
    return page.url.startsWith(route(routeName))
}

// Проверка авторизации
const isAuthenticated = computed(() => {
    return page.props.auth?.user !== null && page.props.auth?.user !== undefined
})

// Определение роута dashboard в зависимости от роли
const dashboardRoute = computed(() => {
    if (!isAuthenticated.value) {
        return route('login')
    }

    const roles = page.props.auth.user.roles || []

    if (roles.includes('admin') || roles.includes('teacher')) {
        return route('admin.dashboard')
    } else if (roles.includes('student')) {
        return route('student.dashboard')
    } else if (roles.includes('partner')) {
        return route('partner.dashboard')
    }

    return route('login')
})
</script>
