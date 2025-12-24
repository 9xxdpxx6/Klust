<template>
    <MobileContainer :padding="false">
        <div :class="[
            isMobile ? 'space-y-4' : 'space-y-6'
        ]">
            <Head :title="`Кейс: ${caseData.title}`" />

            <!-- Заголовок с действиями -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <div :class="[
                        'flex items-start justify-between',
                        isMobile ? 'flex-col gap-4' : ''
                    ]">
                        <div class="flex-1">
                            <nav class="flex mb-4" aria-label="Breadcrumb">
                                <ol :class="[
                                    'flex items-center',
                                    isMobile ? 'space-x-1' : 'space-x-2'
                                ]">
                                    <li>
                                        <Link :href="route('partner.cases.index')" :class="[
                                            'text-indigo-200 hover:text-white transition-colors',
                                            isMobile ? 'text-xs' : 'text-sm'
                                        ]">
                                            <span class="font-medium">Кейсы</span>
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
                                            'font-medium text-white',
                                            isMobile ? 'text-xs' : 'text-sm'
                                        ]">{{ caseData.title }}</span>
                                    </li>
                                </ol>
                            </nav>
                            <h1 :class="[
                                'font-bold text-white mb-3',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">{{ caseData.title }}</h1>
                            <div :class="[
                                'flex items-center',
                                isMobile ? 'flex-col gap-2' : 'gap-3'
                            ]">
                                <span :class="[
                                    'px-4 py-1.5 font-semibold rounded-full shadow-sm',
                                    isMobile ? 'text-xs' : 'text-sm',
                                    getStatusBadgeClass(caseData.status)
                                ]">
                                    {{ getStatusLabel(caseData.status) }}
                                </span>
                                <span :class="[
                                    'text-indigo-100',
                                    isMobile ? 'text-xs' : 'text-sm'
                                ]">
                                    Создан {{ formatDate(caseData.created_at) }}
                                </span>
                            </div>
                        </div>
                        <div :class="[
                            'flex gap-2',
                            isMobile ? 'w-full' : 'ml-4'
                        ]">
                            <Link
                                :href="route('partner.cases.edit', caseData.id)"
                                :class="[
                                    'inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium',
                                    isMobile ? 'px-3 py-2 text-sm w-full justify-center' : 'px-4 py-2.5'
                                ]"
                            >
                                <i class="pi pi-pencil"></i>
                                Редактировать
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Статистика - карточки с градиентами -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 4 }">
            <div :class="[
                'bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md border border-blue-200/50 hover:shadow-lg transition-shadow',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div class="flex items-center justify-between">
                    <div>
                        <p :class="[
                            'font-medium text-blue-600 mb-1',
                            isMobile ? 'text-xs' : 'text-sm'
                        ]">Всего заявок</p>
                        <p :class="[
                            'font-bold text-blue-900',
                            isMobile ? 'text-xl' : 'text-3xl'
                        ]">{{ statistics.total_applications || 0 }}</p>
                    </div>
                    <div :class="[
                        'flex items-center justify-center bg-blue-500 rounded-xl',
                        isMobile ? 'w-10 h-10' : 'w-12 h-12'
                    ]">
                        <i :class="[
                            'pi pi-file text-white',
                            isMobile ? 'text-lg' : 'text-xl'
                        ]"></i>
                    </div>
                </div>
            </div>

            <div :class="[
                'bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-md border border-amber-200/50 hover:shadow-lg transition-shadow',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div class="flex items-center justify-between">
                    <div>
                        <p :class="[
                            'font-medium text-amber-600 mb-1',
                            isMobile ? 'text-xs' : 'text-sm'
                        ]">На рассмотрении</p>
                        <p :class="[
                            'font-bold text-amber-900',
                            isMobile ? 'text-xl' : 'text-3xl'
                        ]">{{ statistics.pending_applications || 0 }}</p>
                    </div>
                    <div :class="[
                        'flex items-center justify-center bg-amber-500 rounded-xl',
                        isMobile ? 'w-10 h-10' : 'w-12 h-12'
                    ]">
                        <i :class="[
                            'pi pi-clock text-white',
                            isMobile ? 'text-lg' : 'text-xl'
                        ]"></i>
                    </div>
                </div>
            </div>

            <div :class="[
                'bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md border border-green-200/50 hover:shadow-lg transition-shadow',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div class="flex items-center justify-between">
                    <div>
                        <p :class="[
                            'font-medium text-green-600 mb-1',
                            isMobile ? 'text-xs' : 'text-sm'
                        ]">Одобрено</p>
                        <p :class="[
                            'font-bold text-green-900',
                            isMobile ? 'text-xl' : 'text-3xl'
                        ]">{{ statistics.accepted_applications || 0 }}</p>
                    </div>
                    <div :class="[
                        'flex items-center justify-center bg-green-500 rounded-xl',
                        isMobile ? 'w-10 h-10' : 'w-12 h-12'
                    ]">
                        <i :class="[
                            'pi pi-check-circle text-white',
                            isMobile ? 'text-lg' : 'text-xl'
                        ]"></i>
                    </div>
                </div>
            </div>

            <div :class="[
                'bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-md border border-purple-200/50 hover:shadow-lg transition-shadow',
                isMobile ? 'p-4' : 'p-6'
            ]">
                <div class="flex items-center justify-between">
                    <div>
                        <p :class="[
                            'font-medium text-purple-600 mb-1',
                            isMobile ? 'text-xs' : 'text-sm'
                        ]">Команд</p>
                        <p :class="[
                            'font-bold text-purple-900',
                            isMobile ? 'text-xl' : 'text-3xl'
                        ]">{{ teams.length || 0 }}</p>
                    </div>
                    <div :class="[
                        'flex items-center justify-center bg-purple-500 rounded-xl',
                        isMobile ? 'w-10 h-10' : 'w-12 h-12'
                    ]">
                        <i :class="[
                            'pi pi-users text-white',
                            isMobile ? 'text-lg' : 'text-xl'
                        ]"></i>
                    </div>
                </div>
            </div>
            </ResponsiveGrid>

            <!-- Основной контент -->
            <ResponsiveGrid :cols="{ mobile: 1, desktop: 3 }" class="gap-6">
                <!-- Описание и навыки -->
                <div :class="[
                    'space-y-6',
                    isMobile ? '' : 'lg:col-span-2'
                ]">
                <!-- Описание кейса -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div :class="[
                        'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                        isMobile ? 'px-4' : ''
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-xl'
                        ]">
                            <i class="pi pi-file-edit text-indigo-600"></i>
                            Описание кейса
                        </h2>
                    </div>
                    <div :class="[
                        'px-6 py-6',
                        isMobile ? 'px-4 py-4' : ''
                    ]">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ caseData.description || 'Описание не указано' }}</p>
                    </div>
                </div>

                <!-- Требуемые навыки -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div :class="[
                        'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                        isMobile ? 'px-4' : ''
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-xl'
                        ]">
                            <i class="pi pi-star text-amber-500"></i>
                            Требуемые навыки
                        </h2>
                    </div>
                    <div :class="[
                        'px-6 py-6',
                        isMobile ? 'px-4 py-4' : ''
                    ]">
                        <div v-if="caseData.skills && caseData.skills.length > 0" class="flex flex-wrap gap-3">
                            <span
                                v-for="skill in caseData.skills"
                                :key="skill.id"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-800 text-sm font-medium rounded-lg border border-indigo-200 shadow-sm"
                            >
                                {{ skill.name }}
                            </span>
                        </div>
                        <div v-else class="text-center py-8">
                            <i class="pi pi-info-circle text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 text-sm">Навыки не указаны</p>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Детали кейса - боковая панель -->
                <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden sticky top-6">
                    <div :class="[
                        'px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200',
                        isMobile ? 'px-4' : ''
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-xl'
                        ]">
                            <i class="pi pi-info-circle text-indigo-600"></i>
                            Детали кейса
                        </h2>
                    </div>
                    <div :class="[
                        'px-6 py-6 space-y-5',
                        isMobile ? 'px-4 py-4' : ''
                    ]">
                        <!-- Дедлайн -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <i class="pi pi-calendar text-red-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Дедлайн</p>
                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(caseData.deadline) || 'Не указан' }}</p>
                                    <p :class="[
                                        'text-xs font-medium mt-1',
                                        isDeadlineSoon(caseData.deadline) ? 'text-red-600' : 'text-gray-500'
                                    ]">
                                        {{ daysUntilDeadline(caseData.deadline) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Размер команды -->
                        <div class="pb-5 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="pi pi-users text-blue-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Размер команды</p>
                                    <p class="text-sm font-medium text-gray-900">{{ caseData.required_team_size || 1 }} человек</p>
                                </div>
                            </div>
                        </div>

                        <!-- Дата создания -->
                        <div>
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-gray-100 rounded-lg">
                                    <i class="pi pi-clock text-gray-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Дата создания</p>
                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(caseData.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </ResponsiveGrid>

            <!-- Заявки -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-visible">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <div :class="[
                        'flex items-center justify-between',
                        isMobile ? 'flex-col gap-4' : ''
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-xl'
                        ]">
                            <i class="pi pi-file text-indigo-600"></i>
                            Заявки на кейс
                        </h2>
                        <div :class="[
                            'flex gap-2',
                            isMobile ? 'flex-col w-full' : 'items-end'
                        ]">
                            <!-- Поиск -->
                            <div class="select-wrapper w-full" :style="isMobile ? '' : 'min-width: 300px;'">
                                <label
                                    for="search-input"
                                    class="block text-sm font-medium mb-1"
                                >
                                    Поиск
                                </label>
                                <div class="relative">
                                    <input
                                        id="search-input"
                                        v-model="applicationFilters.search"
                                        type="text"
                                        placeholder="По имени участника..."
                                        class="w-full min-h-[2.5rem] h-[2.6rem] pl-10 pr-3 py-2 border border-border rounded-md shadow-sm text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 box-border leading-[1.5]"
                                        style="padding-left: 2.75rem;"
                                        @input="debouncedSearch"
                                    />
                                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                </div>
                            </div>
                            
                            <!-- Сортировка -->
                            <div :class="['select-wrapper', isMobile ? 'w-full' : '']">
                                <Select
                                    v-model="applicationFilters.sort"
                                    label="Сортировка"
                                    :options="sortOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="По умолчанию"
                                    @update:modelValue="loadApplications"
                                />
                            </div>
                            
                            <!-- Статус -->
                            <div :class="['select-wrapper', isMobile ? 'w-full' : '']">
                                <Select
                                    v-model="applicationFilters.status"
                                    label="Статус"
                                    :options="statusFilterOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Все статусы"
                                    @update:modelValue="loadApplications"
                                />
                            </div>
                            
                            <!-- Экспорт -->
                            <div class="select-wrapper w-full">
                                <label
                                    for="export-button"
                                    class="block text-sm font-medium mb-1"
                                >
                                    Экспорт
                                </label>
                                <a
                                    id="export-button"
                                    :href="route('partner.cases.applications.export', { case: caseData.id, status: applicationFilters.status || '' })"
                                    class="inline-flex items-center justify-center gap-2 w-full min-h-[2.5rem] h-[2.6rem] px-3 py-2 border border-border rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 box-border leading-[1.5]"
                                >
                                    <i class="pi pi-download"></i>
                                    Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <!-- Кнопка сброса фильтров -->
                    <div :class="[
                        'mb-4 flex',
                        isMobile ? 'justify-center' : 'justify-end'
                    ]">
                        <button
                            @click="resetFilters"
                            :disabled="!hasActiveFilters"
                            :class="[
                                'inline-flex items-center gap-2 font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
                                isMobile ? 'px-3 py-2 text-xs w-full justify-center' : 'px-4 py-2 text-sm'
                            ]"
                        >
                            <i class="pi pi-refresh"></i>
                            Сбросить фильтры
                        </button>
                    </div>
                
                    <div v-if="applications.data.length === 0" :class="[
                        'text-center',
                        isMobile ? 'py-8' : 'py-12'
                    ]">
                    <div class="max-w-md mx-auto">
                        <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                            <i class="pi pi-file text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Заявок пока нет</h3>
                        <p class="text-sm text-gray-500">
                            {{ applicationFilters.search 
                                ? 'По вашему запросу ничего не найдено.' 
                                : applicationFilters.status 
                                    ? `Нет заявок со статусом "${getStatusLabel(applicationFilters.status)}".` 
                                    : 'На этот кейс еще не было подано заявок от студентов.' }}
                        </p>
                    </div>
                </div>

                    <div v-else class="space-y-4">
                    <div
                        v-for="application in applications.data"
                        :key="application.id"
                        class="p-5 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors"
                    >
                            <!-- Верхняя часть: информация о лидере и действия -->
                        <div :class="[
                            'flex items-start mb-4',
                            isMobile ? 'flex-col gap-4' : 'justify-between'
                        ]">
                            <div :class="[
                                'flex items-center gap-4',
                                isMobile ? 'w-full' : 'flex-1'
                            ]">
                                <div class="flex-shrink-0">
                                    <img
                                        v-if="application.leader?.avatar_url"
                                        class="h-12 w-12 rounded-full object-cover border-2 border-gray-200"
                                        :src="application.leader.avatar_url"
                                        :alt="application.leader?.name"
                                    />
                                    <div
                                        v-else
                                        class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center border-2 border-gray-200"
                                    >
                                        <span class="text-white text-sm font-bold">{{ getUserInitials(application.leader?.name) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ application.leader?.name }}</p>
                                    <p class="text-xs text-gray-500">{{ application.leader?.email }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Размер команды: {{ application.team_size || 1 }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Подана: {{ formatDate(application.created_at) }}</p>
                                </div>
                            </div>
                            
                            <div :class="[
                                'flex items-center gap-3',
                                isMobile ? 'w-full flex-wrap justify-between' : 'flex-shrink-0'
                            ]">
                                <span
                                    class="px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap"
                                    :class="getStatusClass(application.status?.name)"
                                >
                                    {{ application.status?.label || application.status?.name }}
                                </span>
                                <div class="flex gap-2">
                                    <button
                                        v-if="application.status?.name === 'pending' && isCaseActive"
                                        @click="approveApplication(application.id)"
                                        :class="[
                                            'bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium whitespace-nowrap',
                                            isMobile ? 'px-3 py-1.5 text-xs flex-1' : 'px-4 py-2 text-sm'
                                        ]"
                                    >
                                        Одобрить
                                    </button>
                                    <button
                                        v-if="application.status?.name === 'pending' && isCaseActive"
                                        @click="rejectApplication(application.id)"
                                        :class="[
                                            'bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium whitespace-nowrap',
                                            isMobile ? 'px-3 py-1.5 text-xs flex-1' : 'px-4 py-2 text-sm'
                                        ]"
                                    >
                                        Отклонить
                                    </button>
                                    
                                    <!-- Кнопка с тремя точками для изменения статуса -->
                                    <div v-if="isCaseActive" class="relative">
                                        <button
                                            @click="toggleStatusMenu(application.id)"
                                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-200 rounded-lg transition-colors"
                                            :class="{ 'bg-gray-200 text-gray-900': openStatusMenuId === application.id }"
                                        >
                                            <i class="pi pi-ellipsis-v"></i>
                                        </button>
                                        
                                        <!-- Dropdown меню -->
                                        <div
                                            v-if="openStatusMenuId === application.id"
                                            @click.stop
                                            class="absolute right-0 top-full mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                                        >
                                            <button
                                                v-for="statusOption in statusOptions"
                                                :key="statusOption.value"
                                                @click="updateApplicationStatus(application.id, statusOption.value)"
                                                :disabled="application.status?.name === statusOption.value"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-50"
                                            >
                                                Пометить как {{ statusOption.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Соответствие навыкам кейса -->
                        <div v-if="caseData.skills && caseData.skills.length > 0" class="pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Соответствие навыкам:</p>
                                <div class="text-xs text-gray-600">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Покрыто: {{ getCoveredSkillsCount(application) }}/{{ caseData.skills.length }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="skill in caseData.skills"
                                    :key="skill.id"
                                    :class="[
                                        'inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors',
                                        isSkillCovered(application, skill.id)
                                            ? 'bg-green-100 text-green-800 border-green-300'
                                            : 'bg-red-100 text-red-800 border-red-300'
                                    ]"
                                    :title="isSkillCovered(application, skill.id) ? 'Навык есть у команды' : 'Навык отсутствует у команды'"
                                >
                                    <i 
                                        :class="[
                                            'mr-1.5 text-xs',
                                            isSkillCovered(application, skill.id) ? 'pi pi-check-circle' : 'pi pi-times-circle'
                                        ]"
                                    ></i>
                                    {{ skill.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Pagination -->
                    <Pagination v-if="applications.links && applications.links.length > 2" :links="applications.links" :class="['mt-6', isMobile ? '' : '']" />
                </div>
            </div>

            <!-- Команды -->
            <div v-if="teams.length > 0" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-xl'
                    ]">
                        <i class="pi pi-users text-indigo-600"></i>
                        Одобренные команды ({{ teams.length }})
                    </h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3 }">
                    <div
                        v-for="team in teams"
                        :key="team.id"
                        class="p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors flex flex-col"
                    >
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="text-md font-semibold text-gray-900">Команда #{{ team.id }}</h4>
                            </div>
                            
                            <!-- Состав команды -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-1.5">Состав:</p>
                                <div class="space-y-0.5">
                                    <!-- Лидер -->
                                    <div class="text-sm font-semibold text-gray-900">
                                        • {{ team.leader?.name }}
                                    </div>
                                    <!-- Участники -->
                                    <div
                                        v-for="member in team.team_members || []"
                                        :key="member.id"
                                        class="text-sm text-gray-700"
                                    >
                                        • {{ member.user?.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Кнопка прижата к низу -->
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <Link
                                :href="route('partner.teams.show', { application: team.id })"
                                class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Подробнее
                            </Link>
                        </div>
                    </div>
                    </ResponsiveGrid>
                </div>
            </div>

            <!-- Секция опасных действий -->
            <div class="bg-white rounded-xl shadow-md border border-red-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-red-50 to-orange-50 border-b border-red-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-exclamation-triangle text-red-600"></i>
                        Опасные действия
                    </h2>
                </div>
                <div :class="[
                    'px-6 py-6 space-y-4',
                    isMobile ? 'px-4 py-4' : ''
                ]">
                    <!-- Архивирование -->
                    <div v-if="caseData.status !== 'archived'" :class="[
                        'flex pb-4 border-b border-gray-200',
                        isMobile ? 'flex-col gap-4' : 'items-center justify-between'
                    ]">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">Архивирование кейса</h3>
                            <p class="text-sm text-gray-600">
                                Кейс будет перемещен в архив и скрыт от студентов. Вы сможете восстановить его позже.
                            </p>
                        </div>
                        <button
                            @click="confirmArchive"
                            :class="[
                                'inline-flex items-center gap-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none transition-colors font-medium shadow-sm',
                                isMobile ? 'w-full justify-center px-4 py-2.5' : 'ml-6 px-4 py-2.5'
                            ]"
                        >
                            <i class="pi pi-archive"></i>
                            Архивировать
                        </button>
                    </div>

                    <!-- Удаление -->
                    <div :class="[
                        'flex',
                        isMobile ? 'flex-col gap-4' : 'items-center justify-between'
                    ]">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">Удаление кейса</h3>
                            <p class="text-sm text-gray-600">
                                Это действие нельзя отменить. Все данные кейса, включая заявки и связанную информацию, будут удалены безвозвратно.
                            </p>
                        </div>
                        <button
                            @click="confirmDelete"
                            :class="[
                                'inline-flex items-center gap-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none transition-colors font-medium shadow-sm',
                                isMobile ? 'w-full justify-center px-4 py-2.5' : 'ml-6 px-4 py-2.5'
                            ]"
                        >
                            <i class="pi pi-trash"></i>
                            Удалить кейс
                        </button>
                    </div>
                </div>
            </div>

        <!-- Модальное окно подтверждения архивирования -->
        <DangerConfirmDialog
            :visible="showArchiveModal"
            @update:visible="showArchiveModal = $event"
            @confirm="archiveCase"
            type="archive"
            title="Подтверждение архивирования"
            message="Вы уверены, что хотите архивировать кейс"
            :item-name="caseData.title"
            confirm-text="Архивировать"
            cancel-text="Отмена"
            :loading="processing"
            loading-text="Архивирование..."
        >
            <p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-3">
                Кейс будет перемещен в архив и скрыт от студентов. Вы сможете восстановить его позже.
            </p>
        </DangerConfirmDialog>

        <!-- Модальное окно подтверждения удаления -->
        <DangerConfirmDialog
            :visible="showDeleteModal"
            @update:visible="showDeleteModal = $event"
            @confirm="deleteCase"
            type="delete"
            title="Подтверждение удаления"
            message="Вы уверены, что хотите удалить кейс"
            :item-name="caseData.title"
            confirm-text="Удалить"
            cancel-text="Отмена"
            :loading="processing"
            loading-text="Удаление..."
        >
            <div v-if="statistics.total_applications > 0" class="mb-4">
                <p class="text-sm text-red-600 font-semibold mb-2">
                    Внимание! На кейс подано {{ statistics.total_applications }} заявок.
                </p>
                <div class="text-xs text-red-700 bg-red-50 rounded-lg p-3">
                    Удаление кейса с активными заявками может привести к потере данных.
                </div>
            </div>
            <p v-else class="text-xs text-red-600 bg-red-50 rounded-lg p-3">
                Это действие нельзя отменить. Все данные кейса будут удалены безвозвратно.
            </p>
        </DangerConfirmDialog>

        <!-- Модалка для одобрения/отклонения заявки -->
        <ApplicationActionDialog
            :visible="showApplicationModal"
            :type="applicationModalType"
            @update:visible="showApplicationModal = $event"
            @confirm="handleApplicationConfirm"
            :loading="applicationProcessing"
            :server-error="page.props.errors?.rejection_reason"
        />
        </div>
    </MobileContainer>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, Head, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Select from '@/Components/UI/Select.vue';
import DangerConfirmDialog from '@/Components/UI/DangerConfirmDialog.vue';
import ApplicationActionDialog from '@/Components/UI/ApplicationActionDialog.vue';
import MobileContainer from '@/Components/Responsive/MobileContainer.vue';
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue';
import { useResponsive } from '@/Composables/useResponsive';
import { route } from 'ziggy-js';

const props = defineProps({
    caseData: {
        type: Object,
        required: true
    },
    applications: {
        type: Object,
        required: true
    },
    teams: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ tab: 'applications' })
    }
});

const page = usePage();
const { isMobile, padding } = useResponsive();

const applicationFilters = ref({
    search: new URLSearchParams(window.location.search).get('search') || '',
    status: new URLSearchParams(window.location.search).get('status') || null,
    sort: new URLSearchParams(window.location.search).get('sort') || 'id_desc'
});

// Удаление и архивирование кейса
const showDeleteModal = ref(false);
const showArchiveModal = ref(false);
const processing = ref(false);

// Модалка для одобрения/отклонения заявок
const showApplicationModal = ref(false);
const applicationModalType = ref('approve'); // 'approve' | 'reject'
const selectedApplicationId = ref(null);
const applicationProcessing = ref(false);

// Меню изменения статуса
const openStatusMenuId = ref(null);
const statusOptions = [
    { label: 'Ожидает', value: 'pending' },
    { label: 'Принято', value: 'accepted' },
    { label: 'Отклонено', value: 'rejected' },
];

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Ожидает', value: 'pending' },
    { label: 'Принято', value: 'accepted' },
    { label: 'Отклонено', value: 'rejected' },
]);

const sortOptions = [
    { label: 'По умолчанию', value: 'id_desc' },
    { label: 'По дате ↑', value: 'created_at_asc' },
    { label: 'По дате ↓', value: 'created_at_desc' },
];

// Проверка наличия активных фильтров
const hasActiveFilters = computed(() => {
    return !!(applicationFilters.value.search || 
              applicationFilters.value.status || 
              (applicationFilters.value.sort && applicationFilters.value.sort !== 'id_desc'));
});

// Сброс фильтров
const resetFilters = () => {
    applicationFilters.value = {
        search: '',
        status: null,
        sort: 'id_desc'
    };
    loadApplications();
};

// Проверка, что кейс еще активен (дедлайн не прошел)
const isCaseActive = computed(() => {
    if (!props.caseData.deadline) return true;
    const deadline = new Date(props.caseData.deadline);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return deadline >= today;
});

const getStatusClass = (status) => {
    switch (status) {
        case 'draft':
        case 'pending':
            return 'bg-gray-100 text-gray-800';
        case 'active':
        case 'accepted':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'archived':
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'draft':
            return 'Черновик';
        case 'pending':
            return 'Ожидает';
        case 'active':
            return 'Активен';
        case 'completed':
            return 'Завершен';
        case 'archived':
            return 'Архив';
        case 'accepted':
            return 'Принято';
        case 'rejected':
            return 'Отклонено';
        default:
            return status;
    }
};

const getStatusBadgeClass = (status) => {
    const classMap = {
        draft: 'bg-gray-100 text-gray-800 border border-gray-200',
        active: 'bg-green-100 text-green-800 border border-green-200',
        completed: 'bg-blue-100 text-blue-800 border border-blue-200',
        archived: 'bg-red-100 text-red-800 border border-red-200',
    };
    return classMap[status] || 'bg-gray-100 text-gray-800 border border-gray-200';
};

const formatDate = (dateString) => {
    if (!dateString) return 'Не указан';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
};

const daysUntilDeadline = (deadline) => {
    if (!deadline) return '';
    try {
        const today = new Date();
        const deadlineDate = new Date(deadline);
        const diffTime = deadlineDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays < 0) return 'Просрочен';
        if (diffDays === 0) return 'Сегодня';
        if (diffDays === 1) return 'Завтра';
        return `Через ${diffDays} дн.`;
    } catch (error) {
        return '';
    }
};

const isDeadlineSoon = (deadline) => {
    if (!deadline) return false;
    try {
        const today = new Date();
        const deadlineDate = new Date(deadline);
        const diffTime = deadlineDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays <= 3 && diffDays >= 0;
    } catch (error) {
        return false;
    }
};

// Debounce для поиска
let searchTimeout = null;
const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        loadApplications();
    }, 500);
};

const loadApplications = () => {
    const params = {};
    
    if (applicationFilters.value.search) {
        params.search = applicationFilters.value.search;
    }
    
    if (applicationFilters.value.status) {
        params.status = applicationFilters.value.status;
    }
    
    // Парсим сортировку (format: "field_order" или "field_field_order")
    const sortValue = applicationFilters.value.sort || 'id_desc';
    const parts = sortValue.split('_');
    const sortOrder = parts.pop(); // Последний элемент - это порядок (asc/desc)
    const sortBy = parts.join('_'); // Остальное - это поле (может быть created_at)
    params.sort_by = sortBy;
    params.sort_order = sortOrder;
    
    // Сохраняем фокус на инпуте, если он есть
    const searchInput = document.getElementById('search-input');
    const hadFocus = document.activeElement === searchInput;
    
    router.get(
        route('partner.cases.show', {
            case: props.caseData.id
        }),
        params,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                // Восстанавливаем фокус после обновления
                if (hadFocus && searchInput) {
                    searchInput.focus();
                }
            }
        }
    );
};

const approveApplication = (applicationId) => {
    selectedApplicationId.value = applicationId;
    applicationModalType.value = 'approve';
    showApplicationModal.value = true;
};

const rejectApplication = (applicationId) => {
    selectedApplicationId.value = applicationId;
    applicationModalType.value = 'reject';
    showApplicationModal.value = true;
};

const toggleStatusMenu = (applicationId) => {
    if (openStatusMenuId.value === applicationId) {
        openStatusMenuId.value = null;
    } else {
        openStatusMenuId.value = applicationId;
    }
};

const updateApplicationStatus = (applicationId, newStatus) => {
    openStatusMenuId.value = null;
    
    router.visit(
        route('partner.cases.applications.status.update', {
            case: props.caseData.id,
            application: applicationId
        }),
        {
            method: 'patch',
            data: { status: newStatus },
            preserveScroll: true,
            onSuccess: () => {
                // Перезагружаем заявки
                router.reload({ only: ['applications', 'statistics'] });
            }
        }
    );
};

// Закрытие меню при клике вне его
const handleClickOutside = (event) => {
    if (openStatusMenuId.value !== null) {
        const target = event.target;
        if (!target.closest('.relative')) {
            openStatusMenuId.value = null;
        }
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

const handleApplicationConfirm = (data) => {
    if (!selectedApplicationId.value) {
        return;
    }
    
    applicationProcessing.value = true;
    
    const routeName = applicationModalType.value === 'approve'
        ? 'partner.cases.applications.approve'
        : 'partner.cases.applications.reject';
    
    const payload = applicationModalType.value === 'reject'
        ? { rejection_reason: data.rejection_reason }
        : {};
    
    router.post(
        route(routeName, { 
            case: props.caseData.id, 
            application: selectedApplicationId.value 
        }), 
        payload,
        {
            preserveScroll: true,
            preserveState: false, // Перезагружаем состояние после успешного действия
            onSuccess: () => {
                showApplicationModal.value = false;
                selectedApplicationId.value = null;
                applicationProcessing.value = false;
                // Редирект уже произошел на сервере, Inertia автоматически обновит URL
            },
            onError: () => {
                applicationProcessing.value = false;
                // При ошибке валидации модальное окно должно остаться открытым
                // чтобы пользователь мог видеть ошибки и исправить их
                // Ошибки валидации доступны через $page.props.errors
            },
            onFinish: () => {
                applicationProcessing.value = false;
            }
        }
    );
};

const confirmArchive = () => {
    showArchiveModal.value = true;
};

const archiveCase = () => {
    processing.value = true;
    router.post(route('partner.cases.archive', { case: props.caseData.id }), {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showArchiveModal.value = false;
            processing.value = false;
        },
        onError: () => {
            alert('Произошла ошибка при архивировании кейса');
            showArchiveModal.value = false;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteCase = () => {
    processing.value = true;
    router.delete(route('partner.cases.destroy', { case: props.caseData.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            processing.value = false;
        },
        onError: () => {
            alert('Произошла ошибка при удалении кейса');
            showDeleteModal.value = false;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        }
    });
};

const getUserInitials = (name) => {
    if (!name) return '??'
    return name
        .split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

// Проверка, покрыт ли навык командой (для заявки)
const isSkillCovered = (application, skillId) => {
    if (!application.covered_skill_ids || !Array.isArray(application.covered_skill_ids)) {
        return false;
    }
    // Преобразуем оба ID в числа для надежного сравнения
    const skillIdNum = Number(skillId);
    return application.covered_skill_ids.some(id => Number(id) === skillIdNum);
}

// Подсчет количества покрытых навыков (для заявки)
const getCoveredSkillsCount = (application) => {
    if (!application.covered_skill_ids || !Array.isArray(application.covered_skill_ids)) {
        return 0;
    }
    return application.covered_skill_ids.length;
}
</script>
