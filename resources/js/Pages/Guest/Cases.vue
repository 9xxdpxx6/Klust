<template>
    <PublicLayout>
        <Head title="Каталог кейсов" />
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
                    <p class="text-text-secondary" :class="{ 'mb-6': !isAuthenticated }">
                        В данный момент нет активных кейсов
                    </p>
                    <Link
                        v-if="!isAuthenticated"
                        :href="route('register')"
                        class="inline-flex px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors font-semibold"
                    >
                        Зарегистрироваться и следить за новыми кейсами
                    </Link>
                </div>

                <!-- Cases Grid -->
                <div v-else>
                    <!-- Info Banner - только для неавторизованных -->
                    <div v-if="!isAuthenticated" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
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
                        <GuestCaseCard
                            v-for="caseItem in cases.data"
                            :key="caseItem.id"
                            :case-data="caseItem"
                            :link-url="route('guest.cases.show', caseItem.id)"
                        />
                    </div>

                    <!-- Pagination -->
                    <div v-if="cases.last_page > 1" class="mt-8">
                        <Pagination :links="paginationLinks" />
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section - только для неавторизованных -->
        <section v-if="!isAuthenticated" class="py-16 bg-kubgtu-white">
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
import { Head, Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import GuestCaseCard from '@/Components/GuestCaseCard.vue'
import Pagination from '@/Components/Pagination.vue'

const page = usePage()

const props = defineProps({
    cases: {
        type: Object,
        required: true,
    },
})

const paginationLinks = computed(() => {
    return props.cases.links || []
})

// Проверка авторизации
const isAuthenticated = computed(() => {
    return page.props.auth?.user !== null && page.props.auth?.user !== undefined
})

// Проверка роли студента
const isStudent = computed(() => {
    if (!isAuthenticated.value) {
        return false
    }
    const roles = page.props.auth.user.roles || []
    return roles.includes('student')
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
