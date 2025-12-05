<template>
    <div class="space-y-6">
        <Head :title="`Пользователь: ${user.name}`" />

        <!-- Заголовок с действиями -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <nav class="flex mb-4" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                <li>
                                    <Link :href="route('admin.users.index')" class="text-indigo-200 hover:text-white transition-colors">
                                        <span class="text-sm font-medium">Пользователи</span>
                                    </Link>
                                </li>
                                <li>
                                    <i class="pi pi-angle-right text-indigo-300 text-sm"></i>
                                </li>
                                <li>
                                    <span class="text-sm font-medium text-white">{{ user.name }}</span>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-3xl font-bold text-white mb-3">{{ user.name }}</h1>
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    :class="getRoleBadgeClass(role.name)"
                                    class="px-4 py-1.5 text-sm font-semibold rounded-full shadow-sm"
                                >
                                    {{ roleTranslations[role.name] || role.name }}
                                </span>
                            </div>
                            <span class="text-indigo-100 text-sm">
                                Зарегистрирован {{ formatDate(user.created_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <Link
                            :href="route('admin.users.edit', user.id)"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium"
                        >
                            <i class="pi pi-pencil"></i>
                            Редактировать
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика - карточки с градиентами -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-md border border-blue-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 mb-1">Навыков</p>
                        <p class="text-3xl font-bold text-blue-900">{{ stats.skills_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-blue-500 rounded-xl">
                        <i class="pi pi-star text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-6 shadow-md border border-amber-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 mb-1">Бейджей</p>
                        <p class="text-3xl font-bold text-amber-900">{{ stats.badges_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-amber-500 rounded-xl">
                        <i class="pi pi-trophy text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 shadow-md border border-green-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 mb-1">Сессий симулятора</p>
                        <p class="text-3xl font-bold text-green-900">{{ stats.simulator_sessions_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-green-500 rounded-xl">
                        <i class="pi pi-desktop text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-md border border-purple-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 mb-1">Заявок на кейсы</p>
                        <p class="text-3xl font-bold text-purple-900">{{ stats.case_applications_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-purple-500 rounded-xl">
                        <i class="pi pi-file-edit text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-6 shadow-md border border-pink-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-pink-600 mb-1">Участий в командах</p>
                        <p class="text-3xl font-bold text-pink-900">{{ stats.team_memberships_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-pink-500 rounded-xl">
                        <i class="pi pi-users text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-md border border-indigo-200/50 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 mb-1">Уведомлений</p>
                        <p class="text-3xl font-bold text-indigo-900">{{ stats.notifications_count || 0 }}</p>
                    </div>
                    <div class="w-12 h-12 flex items-center justify-center bg-indigo-500 rounded-xl">
                        <i class="pi pi-bell text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Основная информация -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Основная информация -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-user text-indigo-600"></i>
                            Основная информация
                        </h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Аватар</p>
                                <div class="flex items-center">
                                    <img
                                        v-if="user.avatar"
                                        class="h-20 w-20 rounded-full border-2 border-gray-200"
                                        :src="user.avatar"
                                        :alt="user.name"
                                    />
                                    <div
                                        v-else
                                        class="h-20 w-20 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200"
                                    >
                                        <span class="text-white text-xl font-bold">{{ getUserInitials(user.name) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Имя</p>
                                <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email</p>
                                <p class="text-sm font-medium text-gray-900">{{ user.email }}</p>
                                <span
                                    v-if="user.email_verified_at"
                                    class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                >
                                    <i class="pi pi-check-circle text-xs"></i>
                                    Подтвержден
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"
                                >
                                    <i class="pi pi-times-circle text-xs"></i>
                                    Не подтвержден
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">KUBGTU ID</p>
                                <p class="text-sm font-medium text-gray-900">{{ user.kubgtu_id || 'Не указан' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Курс</p>
                                <span
                                    v-if="user.student_profile?.course"
                                    :class="getCourseBadgeClass(user.student_profile.course)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg"
                                >
                                    <i class="pi pi-calendar text-xs"></i>
                                    {{ user.student_profile.course }} курс
                                </span>
                                <span v-else class="text-sm text-gray-500">Не указан</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Дата регистрации</p>
                                <p class="text-sm font-medium text-gray-900">{{ formatDate(user.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Профили пользователя (только если больше 1) -->
                <div v-if="profileCount > 1" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-id-card text-indigo-600"></i>
                            Профили пользователя
                        </h2>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Студенческий профиль -->
                            <div v-if="user.student_profile" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h3 class="text-sm font-semibold text-blue-900 mb-3">Студенческий профиль</h3>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Факультет</p>
                                        <p class="text-sm font-medium text-blue-900">
                                            <span v-if="user.student_profile.faculty">
                                                {{ user.student_profile.faculty.name || user.student_profile.faculty }}
                                            </span>
                                            <span v-else>Не указан</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Курс</p>
                                        <p class="text-sm font-medium text-blue-900">{{ user.student_profile.course ? `${user.student_profile.course} курс` : 'Не указан' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Группа</p>
                                        <p class="text-sm font-medium text-blue-900">{{ user.student_profile.group || 'Не указана' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Специальность</p>
                                        <p class="text-sm font-medium text-blue-900">{{ user.student_profile.specialization || 'Не указана' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Профиль партнера -->
                            <div v-if="user.partner_profile" class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <h3 class="text-sm font-semibold text-green-900 mb-3">Профиль партнера</h3>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-xs text-green-600 mb-1">Компания</p>
                                        <p class="text-sm font-medium text-green-900">{{ user.partner_profile.company_name || 'Не указана' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-green-600 mb-1">Должность</p>
                                        <p class="text-sm font-medium text-green-900">{{ user.partner_profile.position || 'Не указана' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Профиль преподавателя -->
                            <div v-if="user.teacher_profile" class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                                <h3 class="text-sm font-semibold text-purple-900 mb-3">Профиль преподавателя</h3>
                                <div class="space-y-2">
                                    <div>
                                        <p class="text-xs text-purple-600 mb-1">Кафедра</p>
                                        <p class="text-sm font-medium text-purple-900">{{ user.teacher_profile.department || 'Не указана' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-purple-600 mb-1">Должность</p>
                                        <p class="text-sm font-medium text-purple-900">{{ user.teacher_profile.position || 'Не указана' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Навыки и бейджи -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Навыки -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <i class="pi pi-star text-amber-500"></i>
                                Навыки
                            </h2>
                        </div>
                        <div class="px-6 py-6">
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
                            <div v-else class="text-center text-gray-500 py-8">
                                <i class="pi pi-info-circle text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm">Навыки не добавлены</p>
                            </div>
                        </div>
                    </div>

                    <!-- Бейджи -->
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                <i class="pi pi-trophy text-amber-500"></i>
                                Бейджи
                            </h2>
                        </div>
                        <div class="px-6 py-6">
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
                                    <div class="font-medium text-gray-900 text-sm">{{ badge.name }}</div>
                                    <div class="text-xs text-gray-500">{{ formatDate(badge.pivot.earned_at) }}</div>
                                </div>
                            </div>
                            <div v-else class="text-center text-gray-500 py-8">
                                <i class="pi pi-info-circle text-4xl text-gray-300 mb-3"></i>
                                <p class="text-sm">Бейджи не получены</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Участие в кейсах -->
                <div v-if="user.case_applications?.length || user.case_team_members?.length" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-briefcase text-indigo-600"></i>
                            Участие в кейсах
                        </h2>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <!-- Как лидер -->
                        <div v-if="user.case_applications && user.case_applications.length">
                            <h4 class="font-medium text-gray-700 mb-3">В качестве лидера команды</h4>
                            <div class="space-y-3">
                                <div
                                    v-for="application in user.case_applications"
                                    :key="application.id"
                                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ application.case.title }}</div>
                                            <div class="text-sm text-gray-500 mt-1">Партнер: {{ application.case.partner?.company_name || 'Не указан' }}</div>
                                            <div class="text-sm text-gray-500">Статус: {{ application.status?.label || getStatusText(application.status?.name) }}</div>
                                        </div>
                                        <span
                                            :class="getStatusBadgeClass(application.status?.name)"
                                            class="px-3 py-1 text-xs font-semibold rounded-full"
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
                                    class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <div class="font-medium text-gray-900">{{ member.application.case.title }}</div>
                                    <div class="text-sm text-gray-500 mt-1">Лидер: {{ member.application.leader.name }}</div>
                                    <div class="text-sm text-gray-500">Статус: {{ member.application.status?.label || getStatusText(member.application.status?.name) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Детали пользователя - боковая панель -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="pi pi-info-circle text-indigo-600"></i>
                            Детали пользователя
                        </h2>
                    </div>
                    <div class="px-6 py-6 space-y-5">
                        <!-- Роли -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg">
                                    <i class="pi pi-shield text-indigo-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Роли</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            :class="getRoleBadgeClass(role.name)"
                                            class="px-2.5 py-1 text-xs font-semibold rounded-lg"
                                        >
                                            {{ roleTranslations[role.name] || role.name }}
                                        </span>
                                        <span v-if="!user.roles.length" class="text-sm text-gray-500">Нет ролей</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email статус -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-lg">
                                    <i class="pi pi-envelope text-green-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Email статус</p>
                                    <span
                                        v-if="user.email_verified_at"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                    >
                                        <i class="pi pi-check-circle text-xs"></i>
                                        Подтвержден
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
                                    >
                                        <i class="pi pi-times-circle text-xs"></i>
                                        Не подтвержден
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Профиль (если только один) -->
                        <div v-if="profileCount === 1" class="pb-5 border-b border-gray-100">
                            <!-- Студенческий профиль -->
                            <div v-if="user.student_profile">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg">
                                        <i class="pi pi-user text-blue-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Студенческий профиль</p>
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Факультет</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    <span v-if="user.student_profile.faculty">
                                                        {{ user.student_profile.faculty.name || user.student_profile.faculty }}
                                                    </span>
                                                    <span v-else>Не указан</span>
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Группа</p>
                                                <p class="text-sm font-medium text-gray-900">{{ user.student_profile.group || 'Не указана' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Специальность</p>
                                                <p class="text-sm font-medium text-gray-900">{{ user.student_profile.specialization || 'Не указана' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Профиль партнера -->
                            <div v-if="user.partner_profile">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-lg">
                                        <i class="pi pi-building text-green-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Профиль партнера</p>
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Компания</p>
                                                <p class="text-sm font-medium text-gray-900">{{ user.partner_profile.company_name || 'Не указана' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Должность</p>
                                                <p class="text-sm font-medium text-gray-900">{{ user.partner_profile.position || 'Не указана' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Профиль преподавателя -->
                            <div v-if="user.teacher_profile">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg">
                                        <i class="pi pi-graduation-cap text-purple-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Профиль преподавателя</p>
                                        <div class="space-y-2">
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Кафедра</p>
                                                <p class="text-sm font-medium text-gray-900">{{ user.teacher_profile.department || 'Не указана' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 mb-0.5">Должность</p>
                                                <p class="text-sm font-medium text-gray-900">{{ user.teacher_profile.position || 'Не указана' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Дата регистрации -->
                        <div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-lg">
                                    <i class="pi pi-clock text-gray-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Дата регистрации</p>
                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(user.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Секция опасных действий -->
        <div class="bg-white rounded-xl shadow-md border border-red-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-red-200">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-600"></i>
                    Опасные действия
                </h2>
            </div>
            <div class="px-6 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Удаление пользователя</h3>
                        <p class="text-sm text-gray-600">
                            Это действие нельзя отменить. Все данные пользователя будут удалены безвозвратно.
                        </p>
                    </div>
                    <button
                        @click="confirmDelete"
                        class="ml-6 inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none transition-colors font-medium shadow-sm"
                    >
                        <i class="pi pi-trash"></i>
                        Удалить пользователя
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
        <DangerConfirmDialog
            :visible="showDeleteModal"
            @update:visible="showDeleteModal = $event"
            @confirm="deleteUser"
            type="delete"
            title="Подтверждение удаления"
            message="Вы уверены, что хотите удалить пользователя"
            :item-name="user.name"
            confirm-text="Удалить"
            cancel-text="Отмена"
            :loading="processing"
            loading-text="Удаление..."
            :disabled="blockingData?.has_blocking_data"
        >
            <div v-if="blockingData?.has_blocking_data" class="mb-4">
                <p class="text-sm text-red-600 font-semibold mb-2">
                    Внимание! Невозможно удалить пользователя:
                </p>
                <div class="text-xs text-red-700 bg-red-50 rounded-lg p-3 space-y-1">
                    <div v-if="blockingData.active_applications_count > 0">
                        • Пользователь является лидером команды в {{ blockingData.active_applications_count }} 
                        {{ pluralize(blockingData.active_applications_count, 'активной заявке', 'активных заявках', 'активных заявках') }} на кейсы
                    </div>
                    <div v-if="blockingData.active_team_memberships_count > 0">
                        • Пользователь участвует в {{ blockingData.active_team_memberships_count }} 
                        {{ pluralize(blockingData.active_team_memberships_count, 'активной команде', 'активных командах', 'активных командах') }}
                    </div>
                    <div v-if="blockingData.active_cases_count > 0">
                        • У партнера есть {{ blockingData.active_cases_count }} 
                        {{ pluralize(blockingData.active_cases_count, 'активный кейс', 'активных кейса', 'активных кейсов') }}
                    </div>
                </div>
            </div>
            <p v-else class="text-xs text-red-600 bg-red-50 rounded-lg p-3">
                Это действие нельзя отменить. Все данные пользователя будут удалены безвозвратно.
            </p>
        </DangerConfirmDialog>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import DangerConfirmDialog from '@/Components/UI/DangerConfirmDialog.vue'
import { route } from 'ziggy-js'

const props = defineProps({
    user: Object,
    stats: Object,
    blockingData: {
        type: Object,
        default: () => ({
            has_blocking_data: false,
            active_applications_count: 0,
            active_team_memberships_count: 0,
            active_cases_count: 0,
        })
    },
    roleTranslations: {
        type: Object,
        default: () => ({})
    },
    roleColors: {
        type: Object,
        default: () => ({})
    },
    courseColors: {
        type: Object,
        default: () => ({})
    },
})

const showDeleteModal = ref(false)
const processing = ref(false)

// Подсчет количества профилей
const profileCount = computed(() => {
    let count = 0
    if (props.user?.student_profile) count++
    if (props.user?.partner_profile) count++
    if (props.user?.teacher_profile) count++
    return count
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

const getRoleBadgeClass = (roleName) => {
    const classMap = {
        'admin': 'bg-red-100 text-red-800 border border-red-200',
        'partner': 'bg-blue-100 text-blue-800 border border-blue-200',
        'student': 'bg-green-100 text-green-800 border border-green-200',
        'teacher': 'bg-amber-100 text-amber-800 border border-amber-200',
    }
    return classMap[roleName] || 'bg-gray-100 text-gray-800 border border-gray-200'
}

const getCourseBadgeClass = (course) => {
    const classMap = {
        1: 'bg-green-100 text-green-800 border border-green-200',
        2: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        3: 'bg-orange-100 text-orange-800 border border-orange-200',
        4: 'bg-red-100 text-red-800 border border-red-200',
        5: 'bg-blue-100 text-blue-800 border border-blue-200',
        6: 'bg-amber-100 text-amber-800 border border-amber-200',
    }
    return classMap[course] || 'bg-gray-100 text-gray-800 border border-gray-200'
}

const pluralize = (count, one, two, five) => {
    const mod10 = count % 10
    const mod100 = count % 100

    if (mod100 >= 11 && mod100 <= 19) {
        return five
    }
    if (mod10 === 1) {
        return one
    }
    if (mod10 >= 2 && mod10 <= 4) {
        return two
    }
    return five
}

const confirmDelete = () => {
    showDeleteModal.value = true
}

const deleteUser = () => {
    processing.value = true
    router.delete(route('admin.users.destroy', props.user.id), {
        // Inertia::location() в контроллере уже делает редирект,
        // поэтому onSuccess не нужен
        onError: () => {
            processing.value = false
        },
        onFinish: () => {
            processing.value = false
        },
    })
}
</script>
