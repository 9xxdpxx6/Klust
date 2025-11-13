<template>
    <PublicLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary to-primary-dark text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
                    Каталог кейсов
                </h1>
                <p class="text-xl text-white/90">
                    Реальные проекты от партнеров-компаний
                </p>
            </div>
        </section>

        <!-- Cases Section -->
        <section class="py-12 bg-surface">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="cases.data.length === 0" class="text-center py-20">
                    <svg class="w-24 h-24 text-text-tertiary mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="text-2xl font-semibold text-text-primary mb-2">
                        Нет доступных кейсов
                    </h3>
                    <p class="text-text-secondary mb-6">
                        В данный момент нет активных кейсов
                    </p>
                    <Link
                        :href="route('register')"
                        class="inline-flex px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors font-semibold"
                    >
                        Зарегистрироваться и следить за новыми кейсами
                    </Link>
                </div>

                <!-- Cases Grid -->
                <div v-else>
                    <!-- Info Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-blue-900 mb-1">
                                    Хотите подать заявку на кейс?
                                </h4>
                                <p class="text-blue-800 text-sm">
                                    Зарегистрируйтесь на платформе, чтобы подавать заявки на кейсы и работать над реальными проектами.
                                    <Link :href="route('register')" class="font-semibold underline hover:no-underline">
                                        Зарегистрироваться
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cases -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div
                            v-for="caseItem in cases.data"
                            :key="caseItem.id"
                            class="bg-kubgtu-white p-6 rounded-xl shadow-sm border border-border-light hover:shadow-lg transition-shadow"
                        >
                            <!-- Case Header -->
                            <div class="mb-4">
                                <h3 class="text-xl font-semibold text-text-primary mb-2">
                                    {{ caseItem.title }}
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-text-secondary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>{{ caseItem.partner?.user?.name || 'Партнер' }}</span>
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-text-secondary mb-4 line-clamp-3">
                                {{ caseItem.description }}
                            </p>

                            <!-- Skills -->
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span
                                    v-for="skill in caseItem.skills.slice(0, 4)"
                                    :key="skill.id"
                                    class="px-2 py-1 text-xs font-medium bg-primary/10 text-primary rounded"
                                >
                                    {{ skill.name }}
                                </span>
                                <span
                                    v-if="caseItem.skills.length > 4"
                                    class="px-2 py-1 text-xs font-medium bg-gray-100 text-text-secondary rounded"
                                >
                                    +{{ caseItem.skills.length - 4 }}
                                </span>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-between pt-4 border-t border-border-light">
                                <div class="flex items-center gap-2 text-sm text-text-secondary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span>{{ caseItem.required_team_size }} человек</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-text-secondary">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ formatDate(caseItem.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="cases.last_page > 1" class="flex items-center justify-center gap-2">
                        <Link
                            v-for="page in paginationLinks"
                            :key="page.label"
                            :href="page.url"
                            :class="[
                                'px-4 py-2 rounded-lg font-medium transition-colors',
                                page.active
                                    ? 'bg-primary text-white'
                                    : page.url
                                    ? 'bg-kubgtu-white text-text-primary border border-border-light hover:bg-surface'
                                    : 'bg-gray-100 text-text-tertiary cursor-not-allowed'
                            ]"
                            :disabled="!page.url"
                        >
                            <span v-html="page.label"></span>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 bg-kubgtu-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-text-primary mb-4">
                    Готовы начать?
                </h2>
                <p class="text-lg text-text-secondary mb-8">
                    Зарегистрируйтесь на платформе и начните работу над интересными проектами
                </p>
                <Link
                    :href="route('register')"
                    class="inline-flex px-8 py-4 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors text-lg font-semibold shadow-lg"
                >
                    Зарегистрироваться
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
    cases: {
        type: Object,
        required: true,
    },
})

const paginationLinks = computed(() => {
    return props.cases.links || []
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>
