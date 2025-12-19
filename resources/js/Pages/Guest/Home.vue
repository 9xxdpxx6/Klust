<template>
    <PublicLayout>
        <Head title="Главная" />
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary to-primary-dark text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-extrabold mb-6">
                        Платформа для case-based обучения
                    </h1>
                    <p class="text-xl md:text-2xl text-white/90 mb-8 max-w-3xl mx-auto">
                        Объединяем студентов и партнеров для работы над реальными проектами
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="route('register')"
                            class="px-8 py-4 bg-white text-primary rounded-lg hover:bg-gray-100 transition-colors text-lg font-semibold shadow-lg"
                        >
                            Начать работу
                        </Link>
                        <Link
                            :href="route('guest.cases')"
                            class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-lg hover:bg-white/10 transition-colors text-lg font-semibold"
                        >
                            Посмотреть кейсы
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="py-16 bg-kubgtu-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="text-5xl font-bold text-primary mb-2">
                            {{ statistics.total_cases }}
                        </div>
                        <div class="text-lg text-text-secondary">
                            Активных кейсов
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl font-bold text-primary mb-2">
                            {{ statistics.total_students }}
                        </div>
                        <div class="text-lg text-text-secondary">
                            Студентов на платформе
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-5xl font-bold text-primary mb-2">
                            {{ statistics.total_partners }}
                        </div>
                        <div class="text-lg text-text-secondary">
                            Партнеров-компаний
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 bg-surface">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-text-primary mb-4">
                        Возможности платформы
                    </h2>
                    <p class="text-lg text-text-secondary max-w-2xl mx-auto">
                        Кластер предоставляет все необходимое для эффективного обучения через практику
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-kubgtu-white p-8 rounded-xl shadow-sm border border-border-light">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-text-primary mb-2">
                            Реальные проекты
                        </h3>
                        <p class="text-text-secondary">
                            Работайте над задачами от реальных компаний и получайте практический опыт
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-kubgtu-white p-8 rounded-xl shadow-sm border border-border-light">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-text-primary mb-2">
                            Командная работа
                        </h3>
                        <p class="text-text-secondary">
                            Формируйте команды и развивайте навыки совместной работы над проектами
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-kubgtu-white p-8 rounded-xl shadow-sm border border-border-light">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-text-primary mb-2">
                            Достижения и навыки
                        </h3>
                        <p class="text-text-secondary">
                            Получайте достижения и развивайте профессиональные навыки
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Cases Section -->
        <section v-if="featuredCases.length > 0" class="py-20 bg-kubgtu-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-text-primary mb-2">
                            Актуальные кейсы
                        </h2>
                        <p class="text-lg text-text-secondary">
                            Примеры проектов от партнеров
                        </p>
                    </div>
                    <Link
                        :href="route('guest.cases')"
                        class="hidden md:inline-flex items-center gap-2 text-primary font-semibold hover:text-primary-dark transition-colors"
                    >
                        Все кейсы
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        v-for="caseItem in featuredCases"
                        :key="caseItem.id"
                        class="bg-surface p-6 rounded-xl border border-border-light hover:shadow-lg transition-shadow flex flex-col"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-xl font-semibold text-text-primary pr-2">
                                {{ caseItem.title }}
                            </h3>
                        </div>

                        <p class="text-text-secondary mb-4 line-clamp-3">
                            {{ caseItem.description }}
                        </p>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <span
                                v-for="skill in caseItem.skills.slice(0, 3)"
                                :key="skill.id"
                                class="px-2 py-1 text-xs font-medium bg-primary/10 text-primary rounded"
                            >
                                {{ skill.name }}
                            </span>
                            <span
                                v-if="caseItem.skills.length > 3"
                                class="px-2 py-1 text-xs font-medium bg-gray-100 text-text-secondary rounded"
                            >
                                +{{ caseItem.skills.length - 3 }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-sm text-text-secondary mt-auto pb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>{{ caseItem.partner?.user?.name || 'Партнер' }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>{{ caseItem.required_team_size }} чел.</span>
                            </div>
                        </div>

                        <div class="border-t border-border-light pt-3">
                            <Link
                                :href="route('guest.cases.show', caseItem.id)"
                                class="w-full px-4 py-2 text-sm font-medium text-center text-kubgtu-white bg-primary rounded-lg hover:bg-primary-dark transition-colors inline-block"
                            >
                                Подробнее
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="md:hidden mt-8 text-center">
                    <Link
                        :href="route('guest.cases')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors font-semibold"
                    >
                        Все кейсы
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-br from-primary to-primary-dark text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Готовы начать?
                </h2>
                <p class="text-xl text-white/90 mb-8">
                    Присоединяйтесь к платформе и начните работу над реальными проектами уже сегодня
                </p>
                <Link
                    :href="route('register')"
                    class="inline-flex px-8 py-4 bg-white text-primary rounded-lg hover:bg-gray-100 transition-colors text-lg font-semibold shadow-lg"
                >
                    Зарегистрироваться
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

defineProps({
    statistics: {
        type: Object,
        required: true,
    },
    featuredCases: {
        type: Array,
        default: () => [],
    },
})
</script>
