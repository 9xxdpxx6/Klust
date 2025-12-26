<template>
    <PublicLayout>
        <Head title="Достижения и навыки" />
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary to-primary-dark text-white py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
                    Достижения и навыки
                </h1>
                <p class="text-xl text-white/90">
                    Получайте достижения и развивайте профессиональные навыки
                </p>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-16 bg-kubgtu-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Skills Section -->
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-text-primary mb-6">
                        Система навыков
                    </h2>
                    <p class="text-lg text-text-secondary mb-6 leading-relaxed">
                        На платформе вы можете развивать профессиональные навыки через работу над реальными проектами и прохождение симуляторов. Каждое выполненное действие приносит очки навыков, которые повышают ваш уровень в соответствующих областях.
                    </p>
                    <p class="text-text-secondary leading-relaxed mb-4">
                        <strong class="text-text-primary">Очки за выполнение кейсов</strong> — за каждый успешно завершенный проект вы получаете очки навыков в соответствующих технологиях и областях.
                    </p>
                    <p class="text-text-secondary leading-relaxed mb-4">
                        <strong class="text-text-primary">Очки за прохождение симуляторов</strong> — интерактивные обучающие модули также приносят очки навыков, помогая вам развивать компетенции в различных направлениях.
                    </p>
                    <p class="text-text-secondary leading-relaxed">
                        <strong class="text-text-primary">Повышение уровня навыков</strong> — накапливая очки, вы повышаете уровень своих навыков, что отражается в вашем профиле и делает вас более привлекательным кандидатом для партнеров.
                    </p>
                </div>

                <!-- Achievements Section -->
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-text-primary mb-6">
                        Система достижений
                    </h2>
                    <p class="text-lg text-text-secondary mb-8 leading-relaxed">
                        Платформа награждает ваши успехи специальными бейджами, которые отражают ваши достижения и уровень мастерства в различных областях.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-text-primary mb-2">
                                    Автоматические бейджи
                                </h3>
                                <p class="text-text-secondary leading-relaxed">
                                    Бейджи автоматически присваиваются при достижении определенных порогов. Например, за завершение определенного количества кейсов, достижение высокого уровня навыка или участие в симуляторах. Система отслеживает ваш прогресс и награждает вас соответствующими достижениями.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-text-primary mb-2">
                                    Портфолио достижений
                                </h3>
                                <p class="text-text-secondary leading-relaxed">
                                    Все ваши бейджи и развитые навыки отображаются в вашем профиле, создавая профессиональное портфолио, которое можно показать будущим работодателям. Это наглядное подтверждение ваших компетенций и активной работы на платформе.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-text-primary mb-2">
                                    Мотивация к развитию
                                </h3>
                                <p class="text-text-secondary leading-relaxed">
                                    Система достижений мотивирует вас участвовать в новых проектах, пробовать разные области и постоянно развивать свои навыки. Получение новых бейджей становится дополнительным стимулом для активной работы и профессионального роста.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- How to Earn -->
                <div class="bg-primary/5 border border-primary/20 rounded-xl p-6">
                    <h3 class="text-xl font-semibold text-text-primary mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Как заработать достижения
                    </h3>
                    <ul class="space-y-3 text-text-secondary">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Завершайте проекты и кейсы — получайте очки навыков и специальные бейджи</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Проходите симуляторы для развития навыков в различных областях</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Достигайте высоких уровней навыков — получайте бейджи за мастерство</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Участвуйте в разных типах проектов для получения разнообразных навыков</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-gradient-to-br from-primary to-primary-dark text-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    Начните развивать навыки уже сегодня
                </h2>
                <p class="text-xl text-white/90 mb-8">
                    Присоединяйтесь к платформе и зарабатывайте свои первые достижения
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="route('register')"
                        class="inline-flex px-8 py-4 bg-white text-primary rounded-lg hover:bg-gray-100 transition-colors text-lg font-semibold shadow-lg"
                    >
                        Зарегистрироваться
                    </Link>
                    <Link
                        :href="route('guest.students.how-it-works')"
                        class="inline-flex px-8 py-4 bg-transparent border-2 border-white text-white rounded-lg hover:bg-white/10 transition-colors text-lg font-semibold"
                    >
                        Узнать больше
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PublicLayout from '@/Layouts/PublicLayout.vue'
</script>

