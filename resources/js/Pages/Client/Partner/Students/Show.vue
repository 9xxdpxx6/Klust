<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head :title="`Профиль: ${student.name}`" />
            
            <!-- Заголовок -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <div class="flex-1 min-w-0">
                        <nav class="flex mb-4" aria-label="Breadcrumb">
                            <ol :class="[
                                'flex items-center',
                                isMobile ? 'space-x-1' : 'space-x-2'
                            ]">
                                <li>
                                    <Link :href="route('partner.teams.index')" :class="[
                                        'text-indigo-200 hover:text-white transition-colors',
                                        isMobile ? 'text-xs' : 'text-sm'
                                    ]">
                                        <span class="font-medium">Команды</span>
                                    </Link>
                                </li>
                                <li>
                                    <i :class="[
                                        'pi pi-chevron-right text-indigo-300',
                                        isMobile ? 'text-xs' : ''
                                    ]"></i>
                                </li>
                                <li>
                                    <span :class="[
                                        'font-medium text-white truncate',
                                        isMobile ? 'text-xs' : 'text-sm'
                                    ]">{{ student.name }}</span>
                                </li>
                            </ol>
                        </nav>
                        <h1 :class="[
                            'font-bold text-white mb-2 truncate',
                            isMobile ? 'text-2xl' : 'text-3xl'
                        ]">{{ student.name }}</h1>
                        <p :class="[
                            'text-indigo-100',
                            isMobile ? 'text-xs' : 'text-sm'
                        ]">{{ student.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 4 }" :class="isMobile ? 'gap-4' : 'gap-4'">
                <div :class="[
                    'bg-white rounded-xl shadow-md border border-gray-200',
                    isMobile ? 'p-4' : 'p-6'
                ]">
                    <p :class="[
                        'font-medium text-gray-500',
                        isMobile ? 'text-xs' : 'text-sm'
                    ]">Всего баллов</p>
                    <p :class="[
                        'mt-1 font-bold text-gray-900',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">{{ statistics.total_points || 0 }}</p>
                </div>
                <div :class="[
                    'bg-white rounded-xl shadow-md border border-gray-200',
                    isMobile ? 'p-4' : 'p-6'
                ]">
                    <p :class="[
                        'font-medium text-gray-500',
                        isMobile ? 'text-xs' : 'text-sm'
                    ]">Навыков</p>
                    <p :class="[
                        'mt-1 font-bold text-gray-900',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">{{ statistics.skills_count || 0 }}</p>
                </div>
                <div :class="[
                    'bg-white rounded-xl shadow-md border border-gray-200',
                    isMobile ? 'p-4' : 'p-6'
                ]">
                    <p :class="[
                        'font-medium text-gray-500',
                        isMobile ? 'text-xs' : 'text-sm'
                    ]">Достижений</p>
                    <p :class="[
                        'mt-1 font-bold text-gray-900',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">{{ statistics.badges_count || 0 }}</p>
                </div>
                <div :class="[
                    'bg-white rounded-xl shadow-md border border-gray-200',
                    isMobile ? 'p-4' : 'p-6'
                ]">
                    <p :class="[
                        'font-medium text-gray-500',
                        isMobile ? 'text-xs' : 'text-sm'
                    ]">Завершенных кейсов</p>
                    <p :class="[
                        'mt-1 font-bold text-gray-900',
                        isMobile ? 'text-2xl' : 'text-3xl'
                    ]">{{ statistics.completed_cases || 0 }}</p>
                </div>
            </ResponsiveGrid>

            <!-- Profile Info -->
            <div :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">Информация о студенте</h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2 }" class="gap-6">
                        <div>
                            <div :class="[
                                'flex items-center',
                                isMobile ? 'space-x-3' : 'space-x-4'
                            ]">
                                <div class="flex-shrink-0">
                                    <img
                                        v-if="student.avatar_url || student.avatar"
                                        :class="[
                                            'rounded-full object-cover border-2 border-gray-200',
                                            isMobile ? 'h-16 w-16' : 'h-20 w-20'
                                        ]"
                                        :src="student.avatar_url || student.avatar"
                                        :alt="student.name"
                                    />
                                    <div
                                        v-else
                                        :class="[
                                            'rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200',
                                            isMobile ? 'h-16 w-16' : 'h-20 w-20'
                                        ]"
                                    >
                                        <span :class="[
                                            'text-white font-bold',
                                            isMobile ? 'text-base' : 'text-lg'
                                        ]">{{ getUserInitials(student.name) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p :class="[
                                        'font-semibold text-gray-900',
                                        isMobile ? 'text-base' : 'text-lg'
                                    ]">{{ student.name }}</p>
                                    <p :class="[
                                        'text-gray-500',
                                        isMobile ? 'text-xs' : 'text-sm'
                                    ]">{{ student.email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div v-if="studentProfile?.faculty">
                                <p :class="[
                                    'font-medium text-gray-500',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">Факультет</p>
                                <p :class="[
                                    'text-gray-900',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">
                                    {{ studentProfile.faculty?.name || studentProfile.faculty }}
                                </p>
                            </div>
                            <div v-if="studentProfile?.course">
                                <p :class="[
                                    'font-medium text-gray-500',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">Курс</p>
                                <p :class="[
                                    'text-gray-900',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">{{ studentProfile.course }} курс</p>
                            </div>
                        </div>
                    </ResponsiveGrid>
                </div>
            </div>

            <!-- Skills -->
            <div v-if="skills && skills.length > 0" :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">Навыки</h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3 }" class="gap-4">
                        <div
                            v-for="skill in skills"
                            :key="skill.id"
                            class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                            :class="isMobile ? 'p-3' : 'p-4'"
                        >
                            <div :class="[
                                'flex items-start justify-between mb-2',
                                isMobile ? 'flex-col gap-2' : ''
                            ]">
                                <h3 :class="[
                                    'font-semibold text-gray-900',
                                    isMobile ? 'text-sm' : 'text-base'
                                ]">{{ skill.name }}</h3>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded font-medium',
                                        isMobile ? 'text-xs' : 'text-xs',
                                        getLevelColor(skill.pivot?.level || 0)
                                    ]"
                                >
                                    Уровень {{ skill.pivot?.level || 0 }}
                                </span>
                            </div>
                            <p :class="[
                                'text-gray-600',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">
                                {{ skill.pivot?.points_earned || 0 }} очков
                            </p>
                        </div>
                    </ResponsiveGrid>
                </div>
            </div>

            <!-- Badges -->
            <div v-if="badges && badges.length > 0" :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">Достижения</h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 2, tablet: 4, desktop: 6 }" class="gap-4">
                        <div
                            v-for="badge in badges"
                            :key="badge.id"
                            class="flex flex-col items-center border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                            :class="isMobile ? 'p-3' : 'p-4'"
                            :title="badge.name"
                        >
                            <!-- Icon as image file -->
                            <img
                                v-if="badge.icon_path"
                                :src="badge.icon_path"
                                :alt="badge.name"
                                :class="[
                                    'mb-2 object-contain',
                                    isMobile ? 'w-12 h-12' : 'w-16 h-16'
                                ]"
                            />
                            <!-- Icon as CSS class (PrimeVue icon) -->
                            <div
                                v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                :class="[
                                    'bg-amber-100 rounded-full flex items-center justify-center mb-2',
                                    isMobile ? 'w-12 h-12' : 'w-16 h-16'
                                ]"
                            >
                                <i :class="[
                                    'text-amber-600',
                                    isMobile ? 'text-2xl' : 'text-4xl',
                                    badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`
                                ]"></i>
                            </div>
                            <!-- Fallback icon -->
                            <div v-else :class="[
                                'bg-amber-100 rounded-full flex items-center justify-center mb-2',
                                isMobile ? 'w-12 h-12' : 'w-16 h-16'
                            ]">
                                <span :class="isMobile ? 'text-2xl' : 'text-4xl'">🏆</span>
                            </div>
                            <p :class="[
                                'font-medium text-gray-900 text-center',
                                isMobile ? 'text-xs' : 'text-xs'
                            ]">{{ badge.name }}</p>
                            <p
                                v-if="badge.pivot?.earned_at"
                                :class="[
                                    'text-gray-500 mt-1',
                                    isMobile ? 'text-xs' : 'text-xs'
                                ]"
                            >
                                {{ formatDate(badge.pivot.earned_at) }}
                            </p>
                        </div>
                    </ResponsiveGrid>
                </div>
            </div>

            <!-- Completed Cases -->
            <div v-if="completedCases && completedCases.length > 0" :class="[
                'bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-medium text-gray-900',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">Завершенные кейсы</h2>
                </div>
                <div :class="[
                    'p-6 space-y-3',
                    isMobile ? 'p-4' : ''
                ]">
                    <div
                        v-for="application in completedCases"
                        :key="application.id"
                        class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                        :class="isMobile ? 'p-3' : 'p-4'"
                    >
                        <div :class="[
                            'flex items-start justify-between',
                            isMobile ? 'flex-col gap-2' : ''
                        ]">
                            <div class="flex-1">
                                <h3 :class="[
                                    'font-semibold text-gray-900',
                                    isMobile ? 'text-sm' : 'text-base'
                                ]">
                                    {{ application.case?.title }}
                                </h3>
                                <p :class="[
                                    'text-gray-500 mt-1',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">
                                    Партнер: {{ application.case?.partner?.company_name || application.case?.partnerUser?.name || 'Не указан' }}
                                </p>
                                <p :class="[
                                    'text-gray-400 mt-1',
                                    isMobile ? 'text-xs' : 'text-xs'
                                ]">
                                    Завершен: {{ formatDate(application.updated_at) }}
                                </p>
                            </div>
                            <span :class="[
                                'px-3 py-1 bg-green-100 text-green-800 font-medium rounded-full flex-shrink-0',
                                isMobile ? 'text-xs w-fit' : 'text-xs'
                            ]">
                                Завершен
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty States -->
            <div
                v-if="(!skills || skills.length === 0) && (!badges || badges.length === 0) && (!completedCases || completedCases.length === 0)"
                :class="[
                    'bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center',
                    isMobile ? 'p-8' : ''
                ]"
            >
                <div class="mx-auto w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-3">
                    <i :class="[
                        'pi pi-info-circle text-gray-400',
                        isMobile ? 'text-3xl' : 'text-4xl'
                    ]"></i>
                </div>
                <p :class="[
                    'text-gray-500',
                    isMobile ? 'text-sm' : ''
                ]">Профиль студента пока пуст</p>
            </div>
        </div>
    </MobileContainer>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import MobileContainer from '@/Components/Responsive/MobileContainer.vue'
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue'
import { useResponsive } from '@/Composables/useResponsive'
import { route } from 'ziggy-js'

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
    studentProfile: {
        type: Object,
        default: null,
    },
    statistics: {
        type: Object,
        required: true,
    },
    skills: {
        type: Array,
        default: () => [],
    },
    badges: {
        type: Array,
        default: () => [],
    },
    completedCases: {
        type: Array,
        default: () => [],
    },
})

const { isMobile, padding } = useResponsive()

const getLevelColor = (level) => {
    if (level >= 8) return 'bg-purple-100 text-purple-800'
    if (level >= 5) return 'bg-blue-100 text-blue-800'
    if (level >= 3) return 'bg-green-100 text-green-800'
    return 'bg-gray-100 text-gray-800'
}

const getUserInitials = (name) => {
    if (!name) return '??'
    return name
        .split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
</script>

