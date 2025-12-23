<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import GuestCaseCard from '@/Components/StudentCaseCard.vue'

const props = defineProps({
    partnerUser: {
        type: Object,
        required: true,
    },
    partnerProfile: {
        type: Object,
        required: true,
    },
    cases: {
        type: Array,
        default: () => [],
    },
})

const companyName = computed(() => props.partnerProfile?.company_name || props.partnerUser?.name || 'Партнёр')

const websiteHref = computed(() => {
    const raw = (props.partnerProfile?.website || '').trim()
    if (!raw) return null
    if (raw.startsWith('http://') || raw.startsWith('https://')) return raw
    return `https://${raw}`
})
</script>

<template>
    <PublicLayout>
        <Head :title="`Профиль партнёра: ${companyName}`" />

        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-primary to-primary-dark text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumbs -->
                <nav class="mb-6 text-sm">
                    <Link :href="route('guest.cases')" class="text-white/80 hover:text-white transition-colors">Каталог кейсов</Link>
                    <span class="mx-2 text-white/60">/</span>
                    <span class="text-white/90">Профиль партнёра</span>
                </nav>

                <div class="flex items-center gap-4">
                    <img
                        v-if="partnerProfile.logo"
                        :src="partnerProfile.logo"
                        :alt="companyName"
                        class="w-16 h-16 rounded-lg object-cover bg-white p-1"
                    />
                    <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center" v-else>
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-4xl md:text-5xl font-extrabold mb-2">{{ companyName }}</h1>
                        <p class="text-xl text-white/90">Партнёр</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-12 bg-surface">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- About Company -->
                        <div class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                            <div class="px-6 py-4 border-b border-border-light">
                                <h2 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    О компании
                                </h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div v-if="partnerProfile.description" class="prose max-w-none text-text-secondary">
                                    {{ partnerProfile.description }}
                                </div>
                                <div v-else class="text-sm text-text-tertiary">
                                    Описание не указано
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-if="partnerProfile.address" class="p-4 bg-surface rounded-lg border border-border-light">
                                        <p class="text-sm text-text-secondary mb-1">Адрес</p>
                                        <p class="text-lg font-semibold text-text-primary">{{ partnerProfile.address }}</p>
                                    </div>
                                    <div v-if="partnerProfile.inn" class="p-4 bg-surface rounded-lg border border-border-light">
                                        <p class="text-sm text-text-secondary mb-1">ИНН</p>
                                        <p class="text-lg font-semibold text-text-primary">{{ partnerProfile.inn }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Cases -->
                        <div v-if="cases.length > 0" class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                            <div class="px-6 py-4 border-b border-border-light">
                                <h2 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-2.489 0-4.77.38-7.008 1.08M21 13.255v-3.87a3.37 3.37 0 00-.93-2.39L13.5 2.5a3.37 3.37 0 00-3 0L3.93 6.985A3.37 3.37 0 003 9.385v3.87m18 0v3.87a3.37 3.37 0 01-.93 2.39l-6.57 4.245a3.37 3.37 0 01-3 0l-6.57-4.245A3.37 3.37 0 013 17.255v-3.87" />
                                    </svg>
                                    Активные кейсы
                                </h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <GuestCaseCard
                                        v-for="caseItem in cases"
                                        :key="caseItem.id"
                                        :case-data="caseItem"
                                        :link-url="route('guest.cases.show', caseItem.id)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Contacts -->
                        <div class="bg-kubgtu-white rounded-xl shadow-sm border border-border-light overflow-hidden">
                            <div class="px-6 py-4 border-b border-border-light">
                                <h3 class="text-lg font-semibold text-text-primary flex items-center gap-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Контакты
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div v-if="partnerProfile.contact_person" class="p-4 bg-surface rounded-lg border border-border-light">
                                    <p class="text-sm text-text-secondary mb-1">Контактное лицо</p>
                                    <p class="text-lg font-semibold text-text-primary">{{ partnerProfile.contact_person }}</p>
                                </div>

                                <div v-if="partnerProfile.contact_phone" class="p-4 bg-surface rounded-lg border border-border-light">
                                    <p class="text-sm text-text-secondary mb-1">Телефон</p>
                                    <p class="text-lg font-semibold text-text-primary">{{ partnerProfile.contact_phone }}</p>
                                </div>

                                <div v-if="websiteHref" class="p-4 bg-surface rounded-lg border border-border-light">
                                    <p class="text-sm text-text-secondary mb-1">Сайт</p>
                                    <a
                                        :href="websiteHref"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-primary font-semibold hover:underline break-all"
                                    >
                                        {{ partnerProfile.website }}
                                    </a>
                                </div>

                                <div v-if="!partnerProfile.contact_person && !partnerProfile.contact_phone && !websiteHref" class="text-sm text-text-tertiary">
                                    Контакты не указаны
                                </div>
                            </div>
                        </div>

                        <!-- CTA Section -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-blue-900 mb-2">
                                    Хотите работать с этим партнёром?
                                </h3>
                                <p class="text-sm text-blue-800 mb-4">
                                    Зарегистрируйтесь на платформе, чтобы подавать заявки на кейсы и работать над реальными проектами.
                                </p>
                                <Link
                                    :href="route('register')"
                                    class="block w-full px-4 py-2 text-center text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors"
                                >
                                    Зарегистрироваться
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
