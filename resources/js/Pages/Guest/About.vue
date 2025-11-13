<template>
    <PublicLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary to-primary-dark text-white py-20">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-6">
                    О проекте Klust
                </h1>
                <p class="text-xl text-white/90">
                    Платформа для case-based обучения, объединяющая студентов КубГТУ и партнеров-компаний
                </p>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="py-20 bg-kubgtu-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-text-primary mb-6">
                        Наша миссия
                    </h2>
                    <p class="text-lg text-text-secondary leading-relaxed">
                        Klust создан для того, чтобы дать студентам возможность работать над реальными проектами
                        от компаний-партнеров, получать практический опыт и развивать профессиональные навыки
                        в условиях, максимально приближенных к реальной работе в индустрии.
                    </p>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-20 bg-surface">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-text-primary mb-4">
                        Как это работает
                    </h2>
                    <p class="text-lg text-text-secondary">
                        Простой процесс от регистрации до успешной работы над проектом
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Step 1 -->
                    <div class="relative">
                        <div class="bg-kubgtu-white p-6 rounded-xl shadow-sm border border-border-light h-full">
                            <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                                1
                            </div>
                            <h3 class="text-xl font-semibold text-text-primary mb-3">
                                Регистрация
                            </h3>
                            <p class="text-text-secondary">
                                Студенты и партнеры регистрируются на платформе и создают свои профили
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative">
                        <div class="bg-kubgtu-white p-6 rounded-xl shadow-sm border border-border-light h-full">
                            <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                                2
                            </div>
                            <h3 class="text-xl font-semibold text-text-primary mb-3">
                                Публикация кейсов
                            </h3>
                            <p class="text-text-secondary">
                                Партнеры публикуют реальные кейсы и задачи из своей работы
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative">
                        <div class="bg-kubgtu-white p-6 rounded-xl shadow-sm border border-border-light h-full">
                            <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                                3
                            </div>
                            <h3 class="text-xl font-semibold text-text-primary mb-3">
                                Формирование команд
                            </h3>
                            <p class="text-text-secondary">
                                Студенты подают заявки на интересующие кейсы, формируя команды
                            </p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative">
                        <div class="bg-kubgtu-white p-6 rounded-xl shadow-sm border border-border-light h-full">
                            <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                                4
                            </div>
                            <h3 class="text-xl font-semibold text-text-primary mb-3">
                                Работа над проектом
                            </h3>
                            <p class="text-text-secondary">
                                Команды работают над решением кейса под руководством партнера
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20 bg-kubgtu-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-text-primary mb-4">
                        Преимущества платформы
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- For Students -->
                    <div>
                        <h3 class="text-2xl font-bold text-text-primary mb-6 flex items-center gap-3">
                            <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                            Для студентов
                        </h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Практический опыт работы над реальными проектами</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Развитие профессиональных навыков и компетенций</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Система достижений и бейджей для мотивации</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Возможность наладить контакты с потенциальными работодателями</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Портфолио успешно завершенных проектов</span>
                            </li>
                        </ul>
                    </div>

                    <!-- For Partners -->
                    <div>
                        <h3 class="text-2xl font-bold text-text-primary mb-6 flex items-center gap-3">
                            <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Для партнеров
                        </h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Доступ к талантливым студентам для решения реальных задач</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Возможность протестировать потенциальных сотрудников</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Укрепление связей с университетом</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Простое управление проектами и командами через платформу</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-success mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-text-secondary">Аналитика и отчетность по проектам</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-br from-primary to-primary-dark text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Присоединяйтесь к Klust
                </h2>
                <p class="text-xl text-white/90 mb-8">
                    Станьте частью экосистемы, объединяющей образование и индустрию
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('register')"
                        class="px-8 py-4 bg-white text-primary rounded-lg hover:bg-gray-100 transition-colors text-lg font-semibold"
                    >
                        Зарегистрироваться
                    </Link>
                    <Link
                        :href="route('guest.cases')"
                        class="px-8 py-4 bg-transparent border-2 border-white text-white rounded-lg hover:bg-white/10 transition-colors text-lg font-semibold"
                    >
                        Посмотреть кейсы
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
</script>
