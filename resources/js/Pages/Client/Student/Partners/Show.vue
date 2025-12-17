<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Button from '@/Components/UI/Button.vue'

const props = defineProps({
    partnerUser: {
        type: Object,
        required: true,
    },
    partnerProfile: {
        type: Object,
        required: true,
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
    <div class="space-y-6">
        <Head :title="`Профиль партнёра: ${companyName}`" />

        <nav class="text-sm flex items-center gap-2 text-text-secondary">
            <Link :href="route('student.cases.index')" class="text-primary hover:underline">
                Каталог кейсов
            </Link>
            <i class="pi pi-angle-right text-xs text-text-muted"></i>
            <span class="text-text-secondary">Профиль партнёра</span>
        </nav>

        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex items-center gap-4 min-w-0">
                        <img
                            v-if="partnerProfile.logo"
                            :src="partnerProfile.logo"
                            :alt="companyName"
                            class="w-16 h-16 rounded-lg object-cover bg-white p-1"
                        />
                        <div class="w-16 h-16 bg-white/20 rounded-lg flex items-center justify-center" v-else>
                            <i class="pi pi-building text-white text-2xl"></i>
                        </div>

                        <div class="min-w-0">
                            <h1 class="text-3xl font-bold text-white truncate">{{ companyName }}</h1>
                            <p class="text-indigo-100 text-sm mt-1">Партнёр</p>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Button
                            variant="white-outline"
                            @click="$inertia.visit(route('student.cases.index'))"
                        >
                            <i class="pi pi-briefcase mr-2"></i>
                            Кейсы
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-file-edit text-indigo-600"></i>
                            О компании
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-if="partnerProfile.description" class="prose max-w-none">
                            {{ partnerProfile.description }}
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Описание не указано
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="partnerProfile.address" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 mb-1">Адрес</p>
                                <p class="text-gray-900 font-semibold">{{ partnerProfile.address }}</p>
                            </div>
                            <div v-if="partnerProfile.inn" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 mb-1">ИНН</p>
                                <p class="text-gray-900 font-semibold">{{ partnerProfile.inn }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-purple-200">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-phone text-purple-600"></i>
                            Контакты
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-if="partnerProfile.contact_person" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Контактное лицо</p>
                            <p class="text-gray-900 font-semibold">{{ partnerProfile.contact_person }}</p>
                        </div>

                        <div v-if="partnerProfile.contact_phone" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Телефон</p>
                            <p class="text-gray-900 font-semibold">{{ partnerProfile.contact_phone }}</p>
                        </div>

                        <div v-if="websiteHref" class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-600 mb-1">Сайт</p>
                            <a
                                :href="websiteHref"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-primary font-semibold hover:underline break-all"
                            >
                                {{ partnerProfile.website }}
                            </a>
                        </div>

                        <div v-if="!partnerProfile.contact_person && !partnerProfile.contact_phone && !websiteHref" class="text-sm text-gray-500">
                            Контакты не указаны
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


