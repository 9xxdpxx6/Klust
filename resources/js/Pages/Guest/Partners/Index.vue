<template>
    <PublicLayout>
        <Head title="Партнеры-компании" />
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary to-primary-dark text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
                    Партнеры-компании
                </h1>
                <p class="text-xl text-white/90">
                    Компании, которые предлагают реальные проекты для студентов
                </p>
            </div>
        </section>

        <!-- Partners Section -->
        <section class="py-12 bg-surface">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div v-if="partners.data.length === 0" class="text-center py-20">
                    <svg class="w-24 h-24 text-text-tertiary mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-2xl font-semibold text-text-primary mb-2">
                        Нет доступных партнеров
                    </h3>
                    <p class="text-text-secondary">
                        В данный момент нет активных партнеров на платформе
                    </p>
                </div>

                <!-- Partners Grid -->
                <div v-else>
                    <!-- Info Banner - только для неавторизованных -->
                    <div v-if="!isAuthenticated" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h4 class="font-semibold text-blue-900 mb-1">
                                    Хотите работать с партнерами?
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

                    <!-- Partners -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div
                            v-for="partner in partners.data"
                            :key="partner.id"
                            class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light hover:shadow-lg transition-shadow flex flex-col p-6"
                        >
                            <!-- Logo and Name -->
                            <div class="flex items-center gap-4 mb-4">
                                <img
                                    v-if="partner.partner_profile?.logo"
                                    :src="partner.partner_profile.logo"
                                    :alt="getCompanyName(partner)"
                                    class="w-16 h-16 rounded-lg object-cover bg-gray-100 p-1"
                                />
                                <div v-else class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-xl font-semibold text-text-primary mb-1 truncate">
                                        {{ getCompanyName(partner) }}
                                    </h3>
                                    <p v-if="partner.partner_profile?.inn" class="text-sm text-text-secondary">
                                        ИНН: {{ partner.partner_profile.inn }}
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <p
                                v-if="partner.partner_profile?.description"
                                class="text-text-secondary mb-4 line-clamp-3 text-sm"
                            >
                                {{ partner.partner_profile.description }}
                            </p>
                            <p v-else class="text-text-tertiary mb-4 text-sm italic">
                                Описание не указано
                            </p>

                            <!-- Additional Info -->
                            <div class="space-y-2 mb-4">
                                <div v-if="partner.partner_profile?.address" class="flex items-start gap-2 text-sm text-text-secondary">
                                    <svg class="w-4 h-4 text-text-tertiary mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="line-clamp-1">{{ partner.partner_profile.address }}</span>
                                </div>
                                <div v-if="partner.partner_profile?.website" class="flex items-center gap-2 text-sm text-text-secondary">
                                    <svg class="w-4 h-4 text-text-tertiary flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <a
                                        :href="getWebsiteUrl(partner.partner_profile.website)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-primary hover:underline truncate"
                                    >
                                        {{ partner.partner_profile.website }}
                                    </a>
                                </div>
                            </div>

                            <!-- Footer with button -->
                            <div class="border-t border-border-light pt-4 mt-auto">
                                <Link
                                    :href="route('partners.show', partner.id)"
                                    class="w-full px-4 py-2 text-sm font-medium text-center text-kubgtu-white bg-primary rounded-lg hover:bg-primary-dark transition-colors inline-block"
                                >
                                    Подробнее
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="partners.last_page > 1" class="mt-8">
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
import Pagination from '@/Components/Pagination.vue'

const page = usePage()

const props = defineProps({
    partners: {
        type: Object,
        required: true,
    },
})

const paginationLinks = computed(() => {
    return props.partners.links || []
})

// Проверка авторизации
const isAuthenticated = computed(() => {
    return page.props.auth?.user !== null && page.props.auth?.user !== undefined
})

const getCompanyName = (partner) => {
    return partner.partner_profile?.company_name || partner.name || 'Партнер'
}

const getWebsiteUrl = (website) => {
    if (!website) return '#'
    const trimmed = website.trim()
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
        return trimmed
    }
    return `https://${trimmed}`
}
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

