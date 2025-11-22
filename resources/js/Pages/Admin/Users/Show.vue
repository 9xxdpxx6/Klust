<template>
    <div>
        <Head :title="`Пользователь: ${user.name}`" />

        <!-- Хлебные крошки -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <Link :href="route('admin.users.index')" class="text-gray-400 hover:text-gray-500">
                                Пользователи
                            </Link>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2 text-gray-400">/</span>
                            <span class="ml-2 text-sm font-medium text-gray-500">{{ user.name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Заголовок страницы и кнопка редактирования -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Профиль пользователя: {{ user.name }}</h1>
            <Link
                :href="route('admin.users.edit', user.id)"
                class="inline-flex items-center justify-center p-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none transition-colors"
                title="Редактировать"
            >
                <i class="pi pi-pencil text-sm"></i>
            </Link>
        </div>

        <!-- Основная информация -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Основная информация</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Детальные данные пользователя</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Аватар</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex items-center">
                                <img
                                    v-if="user.avatar"
                                    class="h-16 w-16 rounded-full"
                                    :src="user.avatar"
                                    :alt="user.name"
                                />
                                <div
                                    v-else
                                    class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center"
                                >
                                    <span class="text-gray-600 text-lg font-medium">{{ getUserInitials(user.name) }}</span>
                                </div>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Имя</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.name }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ user.email }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">KUBGTU ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ user.kubgtu_id || 'Не указан' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Курс</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
              <span v-if="user.course" class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ user.course }} курс
              </span>
                            <span v-else class="text-gray-500">Не указан</span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Роли</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex flex-wrap gap-1">
                <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800"
                >
                  {{ role.name }}
                </span>
                                <span v-if="!user.roles.length" class="text-gray-500">Нет ролей</span>
                            </div>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Статус email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
              <span
                  v-if="user.email_verified_at"
                  class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
              >
                Подтвержден
              </span>
                            <span
                                v-else
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
                            >
                Не подтвержден
              </span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Дата регистрации</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ formatDate(user.created_at) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Профили пользователя -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Студенческий профиль -->
            <div v-if="user.student_profile" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Студенческий профиль</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Факультет:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ user.student_profile.faculty || 'Не указан' }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Специальность:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ user.student_profile.specialization || 'Не указана' }}</span>
                    </div>
                </div>
            </div>

            <!-- Профиль партнера -->
            <div v-if="user.partner_profile" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Профиль партнера</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Компания:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ user.partner_profile.company_name || 'Не указана' }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Должность:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ user.partner_profile.position || 'Не указана' }}</span>
                    </div>
                </div>
            </div>

            <!-- Профиль преподавателя -->
            <div v-if="user.teacher_profile" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Профиль преподавателя</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Кафедра:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ user.teacher_profile.department || 'Не указана' }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Должность:</span>
                        <span class="ml-2 text-sm text-gray-900">{{ user.teacher_profile.position || 'Не указана' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Статистика</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.skills_count }}</div>
                    <div class="text-sm text-gray-500">Навыков</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.badges_count }}</div>
                    <div class="text-sm text-gray-500">Бейджей</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.simulator_sessions_count }}</div>
                    <div class="text-sm text-gray-500">Сессий симулятора</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.case_applications_count }}</div>
                    <div class="text-sm text-gray-500">Заявок на кейсы</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.team_memberships_count }}</div>
                    <div class="text-sm text-gray-500">Участий в командах</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.notifications_count }}</div>
                    <div class="text-sm text-gray-500">Уведомлений</div>
                </div>
            </div>
        </div>

        <!-- Навыки и бейджи -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Навыки -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Навыки</h3>
                <div v-if="user.skills && user.skills.length" class="space-y-3">
                    <div
                        v-for="skill in user.skills"
                        :key="skill.id"
                        class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                    >
                        <div>
                            <div class="font-medium text-gray-900">{{ skill.name }}</div>
                            <div class="text-sm text-gray-500">{{ skill.category === 'hard' ? 'Hard Skill' : 'Soft Skill' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-indigo-600">Уровень {{ skill.pivot.level }}</div>
                            <div class="text-sm text-gray-500">{{ skill.pivot.points_earned }} очков</div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-4">
                    Навыки не добавлены
                </div>
            </div>

            <!-- Бейджи -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Бейджи</h3>
                <div v-if="user.badges && user.badges.length" class="grid grid-cols-2 gap-4">
                    <div
                        v-for="badge in user.badges"
                        :key="badge.id"
                        class="text-center p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex justify-center mb-2">
                            <img
                                v-if="badge.icon_path"
                                :src="badge.icon_path"
                                :alt="badge.name"
                                class="w-12 h-12 object-contain"
                            />
                            <i
                                v-else-if="badge.icon && (badge.icon.startsWith('pi-') || badge.icon.startsWith('fa-'))"
                                :class="['text-[48px] text-yellow-600', badge.icon.startsWith('fa-') ? `pi pi-${badge.icon.replace('fa-', '')}` : `pi ${badge.icon}`]"
                            ></i>
                            <span v-else class="text-2xl">🏆</span>
                        </div>
                        <div class="font-medium text-gray-900">{{ badge.name }}</div>
                        <div class="text-sm text-gray-500">{{ formatDate(badge.pivot.earned_at) }}</div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-4">
                    Бейджи не получены
                </div>
            </div>
        </div>

        <!-- Кейсы пользователя -->
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Участие в кейсах</h3>

            <!-- Как лидер -->
            <div v-if="user.case_applications && user.case_applications.length" class="mb-6">
                <h4 class="font-medium text-gray-700 mb-3">В качестве лидера команды</h4>
                <div class="space-y-3">
                    <div
                        v-for="application in user.case_applications"
                        :key="application.id"
                        class="p-3 border border-gray-200 rounded-lg"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium text-gray-900">{{ application.case.title }}</div>
                                <div class="text-sm text-gray-500">Партнер: {{ application.case.partner.company_name }}</div>
                                <div class="text-sm text-gray-500">Статус: {{ application.status?.label || getStatusText(application.status?.name) }}</div>
                            </div>
                            <span
                                :class="getStatusBadgeClass(application.status?.name)"
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                            >
                {{ application.status?.label || getStatusText(application.status?.name) }}
              </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Как участник команды -->
            <div v-if="user.case_team_members && user.case_team_members.length">
                <h4 class="font-medium text-gray-700 mb-3">В качестве участника команды</h4>
                <div class="space-y-3">
                    <div
                        v-for="member in user.case_team_members"
                        :key="member.id"
                        class="p-3 border border-gray-200 rounded-lg"
                    >
                        <div class="font-medium text-gray-900">{{ member.application.case.title }}</div>
                        <div class="text-sm text-gray-500">Лидер: {{ member.application.leader.name }}</div>
                        <div class="text-sm text-gray-500">Статус: {{ member.application.status?.label || getStatusText(member.application.status?.name) }}</div>
                    </div>
                </div>
            </div>

            <div v-if="!user.case_applications?.length && !user.case_team_members?.length" class="text-center text-gray-500 py-4">
                Пользователь не участвовал в кейсах
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    stats: Object,
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
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

const getStatusText = (status) => {
    const statusMap = {
        'pending': 'На рассмотрении',
        'accepted': 'Принята',
        'rejected': 'Отклонена',
        'draft': 'Черновик',
        'active': 'Активна',
        'completed': 'Завершена',
        'archived': 'В архиве'
    }
    return statusMap[status] || status
}

const getStatusBadgeClass = (status) => {
    const classMap = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'accepted': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'draft': 'bg-gray-100 text-gray-800',
        'active': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'archived': 'bg-gray-100 text-gray-800'
    }
    return classMap[status] || 'bg-gray-100 text-gray-800'
}
</script>
