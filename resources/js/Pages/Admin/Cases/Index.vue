<template>
    <MobileContainer :padding="false">
        <div :class="isMobile ? 'space-y-4' : 'space-y-6'">
            <Head title="Управление кейсами"/>

            <!-- Заголовок с градиентом -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg overflow-hidden">
                <div :class="[
                    'px-6 py-8',
                    isMobile ? 'px-4 py-6' : ''
                ]">
                    <div :class="[
                        'flex items-center justify-between',
                        isMobile ? 'flex-col gap-4' : ''
                    ]">
                        <div>
                            <h1 :class="[
                                'font-bold text-white mb-2',
                                isMobile ? 'text-2xl' : 'text-3xl'
                            ]">Управление кейсами</h1>
                            <p class="text-indigo-100" :class="isMobile ? 'text-sm' : ''">
                                Список всех кейсов в системе
                            </p>
                        </div>
                        <Link
                            :href="route('admin.cases.create')"
                            :class="[
                                'inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white rounded-lg hover:bg-white/20 focus:outline-none transition-all shadow-lg border border-white/20 font-medium',
                                isMobile ? 'px-4 py-2 text-sm w-full justify-center' : 'px-6 py-3'
                            ]"
                        >
                            <i class="pi pi-plus"></i>
                            Создать кейс
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <h2 :class="[
                        'font-bold text-gray-900 flex items-center gap-2',
                        isMobile ? 'text-base' : 'text-lg'
                    ]">
                        <i class="pi pi-filter text-indigo-600"></i>
                        Фильтры поиска
                    </h2>
                </div>
                <div :class="[
                    'p-6',
                    isMobile ? 'p-4' : ''
                ]">
                    <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 5 }">
                        <!-- Поиск -->
                        <div :class="isMobile ? '' : 'lg:col-span-2'">
                            <SearchInput
                                v-model="filters.search"
                                label="Поиск"
                                placeholder="Название или описание"
                                @input="handleSearch"
                            />
                        </div>

                        <!-- Фильтр по статусу -->
                        <div>
                            <Select
                                v-model="filters.status"
                                label="Статус"
                                :options="statusFilterOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Все статусы"
                                @update:modelValue="updateFilters"
                            />
                        </div>

                        <!-- Фильтр по партнеру -->
                        <div>
                            <Select
                                v-model="filters.partner_id"
                                label="Партнер"
                                :options="partnerFilterOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Все партнеры"
                                @update:modelValue="updateFilters"
                            />
                        </div>

                        <!-- Количество на странице -->
                        <div>
                            <Select
                                v-model="filters.perPage"
                                label="На странице"
                                :options="perPageOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Выберите количество"
                                @update:modelValue="updateFilters"
                            />
                        </div>
                    </ResponsiveGrid>

                    <!-- Кнопка сброса фильтров -->
                    <div :class="[
                        'mt-4 flex',
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
                </div>
            </div>

            <!-- Статистика -->
            <ResponsiveGrid :cols="{ mobile: 1, tablet: 2, desktop: 3 }">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md border border-blue-200/50"
                     :class="isMobile ? 'p-4' : 'p-5'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-blue-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Всего кейсов</p>
                            <p :class="[
                                'font-bold text-blue-900',
                                isMobile ? 'text-xl' : 'text-2xl'
                            ]">{{ casesTotal }}</p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-blue-500 rounded-xl',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-briefcase text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md border border-green-200/50"
                     :class="isMobile ? 'p-4' : 'p-5'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-green-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Активных</p>
                            <p :class="[
                                'font-bold text-green-900',
                                isMobile ? 'text-xl' : 'text-2xl'
                            ]">
                                {{ activeCasesCount }}
                            </p>
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
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-md border border-amber-200/50"
                     :class="isMobile ? 'p-4' : 'p-5'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p :class="[
                                'font-medium text-amber-600 mb-1',
                                isMobile ? 'text-xs' : 'text-sm'
                            ]">Черновиков</p>
                            <p :class="[
                                'font-bold text-amber-900',
                                isMobile ? 'text-xl' : 'text-2xl'
                            ]">
                                {{ draftCasesCount }}
                            </p>
                        </div>
                        <div :class="[
                            'flex items-center justify-center bg-amber-500 rounded-xl',
                            isMobile ? 'w-10 h-10' : 'w-12 h-12'
                        ]">
                            <i :class="[
                                'pi pi-file text-white',
                                isMobile ? 'text-lg' : 'text-xl'
                            ]"></i>
                        </div>
                    </div>
                </div>
            </ResponsiveGrid>

            <!-- Таблица кейсов -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div :class="[
                    'px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200',
                    isMobile ? 'px-4' : ''
                ]">
                    <div :class="[
                        'flex items-center',
                        isMobile ? 'flex-col items-start gap-2' : 'flex-row justify-between gap-4'
                    ]">
                        <h2 :class="[
                            'font-bold text-gray-900 flex items-center gap-2',
                            isMobile ? 'text-base' : 'text-lg'
                        ]">
                            <i class="pi pi-list text-indigo-600"></i>
                            Список кейсов
                        </h2>
                        <span :class="[
                            'text-gray-600 bg-white rounded-lg border border-gray-200 whitespace-nowrap',
                            isMobile ? 'text-xs px-2 py-1' : 'text-sm px-3 py-1'
                        ]">
                            Всего: {{ casesTotal }}
                        </span>
                    </div>
                </div>

                <div v-if="casesData && casesData.length > 0">
                    <!-- Заголовки для планшетной/лаптопной версии (md до xl) -->
                    <div class="hidden md:grid xl:hidden md:grid-cols-6 gap-4 px-4 py-3 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <div class="col-span-3 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Кейс
                        </div>
                        <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Партнер / Дедлайн
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Статус
                        </div>
                    </div>

                    <!-- Заголовки для десктопной версии (xl+) -->
                    <div class="hidden xl:grid xl:grid-cols-12 gap-4 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <div class="col-span-3 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Кейс
                        </div>
                        <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Партнер
                        </div>
                        <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Дедлайн
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Команда
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Статус
                        </div>
                        <div class="col-span-2 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Заявки
                        </div>
                        <div class="col-span-1 text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Создан
                        </div>
                    </div>

                    <!-- Список кейсов -->
                    <div class="divide-y divide-gray-200">
                        <!-- Карточка кейса (мобильная) / Строка кейса (десктоп) -->
                        <div
                            v-for="caseItem in casesData"
                            :key="caseItem.id"
                            class="hover:bg-indigo-50/50 cursor-pointer transition-all group"
                            @click="goToCase(caseItem.id)"
                        >
                            <!-- Мобильная версия (карточка) - только для маленьких экранов -->
                            <div class="md:hidden p-5 sm:p-6">
                                <div class="flex items-start gap-4 mb-5">
                                    <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors flex-shrink-0">
                                        <i class="pi pi-briefcase text-indigo-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ caseItem.title }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                            {{ caseItem.description }}
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3.5">
                                    <!-- Партнер -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Партнер:</span>
                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                            <div class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-building text-gray-600 text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ caseItem.partner?.company_name || caseItem.partner?.name || 'Не указан' }}</div>
                                                <div class="text-xs text-gray-500 truncate">{{ caseItem.partner?.contact_person || caseItem.partner?.user?.name || 'Без контакта' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Дедлайн -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Дедлайн:</span>
                                        <div class="flex items-center gap-2 flex-1">
                                            <div class="w-8 h-8 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-calendar text-red-600 text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ formatDate(caseItem.deadline) }}</div>
                                                <div
                                                    :class="[
                                                        'text-xs font-medium mt-0.5',
                                                        isDeadlineSoon(caseItem.deadline) ? 'text-red-600' : 'text-gray-500'
                                                    ]"
                                                >
                                                    {{ daysUntilDeadline(caseItem.deadline) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Команда -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Команда:</span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 border border-blue-200">
                                            <i class="pi pi-users text-xs"></i>
                                            {{ caseItem.required_team_size }} чел.
                                        </span>
                                    </div>

                                    <!-- Статус -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Статус:</span>
                                        <span
                                            :class="[
                                                'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border',
                                                getStatusBadgeClass(caseItem.status)
                                            ]"
                                        >
                                            {{ getStatusLabel(caseItem.status) }}
                                        </span>
                                    </div>

                                    <!-- Заявки -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Заявки:</span>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 flex items-center justify-center bg-purple-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-file-edit text-purple-600 text-xs"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ caseItem.applications_count || 0 }} заявок
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Дата создания -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500 font-medium min-w-[70px]">Создан:</span>
                                        <div class="text-sm text-gray-500">{{ formatDate(caseItem.created_at) }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Планшетная/лаптопная версия (md до xl) - упрощенная таблица -->
                            <div class="hidden md:grid xl:hidden md:grid-cols-6 gap-4 px-4 py-4 items-center">
                                <!-- Кейс -->
                                <div class="col-span-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors flex-shrink-0">
                                            <i class="pi pi-briefcase text-indigo-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                                {{ caseItem.title }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 line-clamp-1">
                                                {{ caseItem.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Партнер и Дедлайн -->
                                <div class="col-span-2">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-building text-gray-600 text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-gray-900 truncate">{{ caseItem.partner?.company_name || caseItem.partner?.name || 'Не указан' }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
                                                <i class="pi pi-calendar text-red-600 text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-gray-900 truncate">{{ formatDate(caseItem.deadline) }}</div>
                                                <div
                                                    :class="[
                                                        'text-xs font-medium mt-0.5',
                                                        isDeadlineSoon(caseItem.deadline) ? 'text-red-600' : 'text-gray-500'
                                                    ]"
                                                >
                                                    {{ daysUntilDeadline(caseItem.deadline) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Статус -->
                                <div class="col-span-1">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 inline-flex text-xs font-semibold rounded-lg border whitespace-nowrap',
                                            getStatusBadgeClass(caseItem.status)
                                        ]"
                                    >
                                        {{ getStatusLabel(caseItem.status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Десктопная версия (xl+) - полная таблица -->
                            <div class="hidden xl:grid xl:grid-cols-12 gap-4 px-6 py-5 items-center">
                                <!-- Кейс -->
                                <div class="col-span-3">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 rounded-lg group-hover:bg-indigo-200 transition-colors flex-shrink-0">
                                            <i class="pi pi-briefcase text-indigo-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                                                {{ caseItem.title }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                {{ caseItem.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Партнер -->
                                <div class="col-span-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-building text-gray-600 text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ caseItem.partner?.company_name || caseItem.partner?.name || 'Не указан' }}</div>
                                            <div class="text-xs text-gray-500 truncate">{{ caseItem.partner?.contact_person || caseItem.partner?.user?.name || 'Без контакта' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Дедлайн -->
                                <div class="col-span-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-calendar text-red-600 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 whitespace-nowrap">{{ formatDate(caseItem.deadline) }}</div>
                                            <div
                                                :class="[
                                                    'text-xs font-medium mt-0.5',
                                                    isDeadlineSoon(caseItem.deadline) ? 'text-red-600' : 'text-gray-500'
                                                ]"
                                            >
                                                {{ daysUntilDeadline(caseItem.deadline) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Команда -->
                                <div class="col-span-1">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 border border-blue-200 whitespace-nowrap">
                                        <i class="pi pi-users text-xs"></i>
                                        {{ caseItem.required_team_size }} чел.
                                    </span>
                                </div>

                                <!-- Статус -->
                                <div class="col-span-1">
                                    <span
                                        :class="[
                                            'px-3 py-1.5 inline-flex text-xs font-semibold rounded-lg border whitespace-nowrap',
                                            getStatusBadgeClass(caseItem.status)
                                        ]"
                                    >
                                        {{ getStatusLabel(caseItem.status) }}
                                    </span>
                                </div>

                                <!-- Заявки -->
                                <div class="col-span-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg flex-shrink-0">
                                            <i class="pi pi-file-edit text-purple-600 text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ caseItem.applications_count || 0 }} заявок
                                        </span>
                                    </div>
                                </div>

                                <!-- Дата создания -->
                                <div class="col-span-1">
                                    <div class="text-sm text-gray-500 whitespace-nowrap">{{ formatDate(caseItem.created_at) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Пагинация -->
                <div v-if="casesLinks && casesLinks.length > 0" :class="['px-6 py-4 border-t border-gray-200 bg-gray-50', isMobile ? 'px-4' : '']">
                    <Pagination :links="casesLinks" />
                </div>
            </div>

            <!-- Сообщение если нет кейсов -->
            <div v-if="!casesData || casesData.length === 0" class="bg-white rounded-xl shadow-md border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                        <i class="pi pi-inbox text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Кейсы не найдены</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        <span v-if="hasActiveFilters">Попробуйте изменить параметры фильтрации</span>
                        <span v-else>Создайте первый кейс, чтобы начать работу</span>
                    </p>
                    <div class="flex gap-3 justify-center">
                        <button
                            v-if="hasActiveFilters"
                            @click="resetFilters"
                            class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Сбросить фильтры
                        </button>
                        <Link
                            v-else
                            :href="route('admin.cases.create')"
                            class="px-6 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                        >
                            Создать кейс
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MobileContainer>
</template>

<script setup>
import {ref, computed} from 'vue'
import {router, Link, Head} from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Select from '@/Components/UI/Select.vue'
import SearchInput from '@/Components/UI/SearchInput.vue'
import Button from 'primevue/button'
import MobileContainer from '@/Components/Responsive/MobileContainer.vue'
import ResponsiveGrid from '@/Components/Responsive/ResponsiveGrid.vue'
import { useResponsive } from '@/Composables/useResponsive'
import {route} from "ziggy-js";

const props = defineProps({
    cases: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    partners: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({})
    },
})

// Computed свойства для безопасного доступа
const { isMobile, padding } = useResponsive()

const casesData = computed(() => props.cases?.data || [])
const casesTotal = computed(() => props.statistics?.total_cases || props.cases?.total || 0)
const casesLinks = computed(() => props.cases?.links || [])
const activeCasesCount = computed(() => props.statistics?.active_cases || 0)
const draftCasesCount = computed(() => props.statistics?.draft_cases || 0)

// Проверка активных фильтров
const hasActiveFilters = computed(() => {
    return filters.value.search !== '' ||
        filters.value.status !== '' ||
        filters.value.partner_id !== '' ||
        filters.value.perPage !== '30'
})

// Безопасная инициализация filters
const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    partner_id: props.filters?.partner_id || '',
    perPage: props.filters?.perPage ? String(props.filters.perPage) : '30',
})

// Функция перехода к конкретному кейсу
const goToCase = (caseId) => {
    router.get(route('admin.cases.show', caseId))
}

// Функция для отображения имени партнера
const getPartnerDisplayName = (partner) => {
    if (!partner) return 'Неизвестный партнер'

    const companyName = partner.company_name || 'Без названия'
    const contactPerson = partner.contact_person || 'Без контакта'

    return `${companyName} (${contactPerson})`
}

// Таймер для дебаунса
let searchTimeout = null

// Обработчик поиска с дебаунсом
const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout)
    }
    searchTimeout = setTimeout(() => {
        updateFilters()
    }, 500)
}

const updateFilters = () => {
    router.get(route('admin.cases.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const statusFilterOptions = computed(() => [
    { label: 'Все статусы', value: '' },
    { label: 'Черновик', value: 'draft' },
    { label: 'Активные', value: 'active' },
    { label: 'Завершенные', value: 'completed' },
    { label: 'Архивные', value: 'archived' },
])

const partnerFilterOptions = computed(() => [
    { label: 'Все партнеры', value: '' },
    ...props.partners.map(partner => ({
        label: getPartnerDisplayName(partner),
        value: partner.id
    }))
])

const perPageOptions = computed(() => [
    { label: 'Отображать по 30', value: '30' },
    { label: 'Отображать по 60', value: '60' },
    { label: 'Отображать по 100', value: '100' },
])

const resetFilters = () => {
    filters.value = {
        search: '',
        status: '',
        partner_id: '',
        perPage: '30',
    }
    updateFilters()
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('ru-RU')
}

const daysUntilDeadline = (deadline) => {
    try {
        const today = new Date()
        const deadlineDate = new Date(deadline)
        const diffTime = deadlineDate - today
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

        if (diffDays < 0) return 'Просрочен'
        if (diffDays === 0) return 'Сегодня'
        if (diffDays === 1) return 'Завтра'
        return `Через ${diffDays} дн.`
    } catch (error) {
        return 'Ошибка даты'
    }
}

const isDeadlineSoon = (deadline) => {
    try {
        const today = new Date()
        const deadlineDate = new Date(deadline)
        const diffTime = deadlineDate - today
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
        return diffDays <= 3 && diffDays >= 0
    } catch (error) {
        return false
    }
}

const getStatusLabel = (status) => {
    const statusMap = {
        draft: 'Черновик',
        active: 'Активный',
        completed: 'Завершен',
        archived: 'Архивный',
    }
    return statusMap[status] || status
}

const getStatusBadgeClass = (status) => {
    const classMap = {
        draft: 'bg-gray-100 text-gray-800 border-gray-200',
        active: 'bg-green-100 text-green-800 border-green-200',
        completed: 'bg-blue-100 text-blue-800 border-blue-200',
        archived: 'bg-red-100 text-red-800 border-red-200',
    }
    return classMap[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
