<template>
    <PartnerLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900">Аналитика</h1>
        </template>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="period-filter" class="block text-sm font-medium text-gray-700 mb-1">
                        Период
                    </label>
                    <select
                        id="period-filter"
                        v-model="filters.period"
                        @change="applyFilters"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    >
                        <option value="7">Последние 7 дней</option>
                        <option value="30">Последние 30 дней</option>
                        <option value="90">Последние 90 дней</option>
                        <option value="all">Весь период</option>
                    </select>
                </div>
                <div>
                    <label for="start-date" class="block text-sm font-medium text-gray-700 mb-1">
                        Начало
                    </label>
                    <input
                        type="date"
                        id="start-date"
                        v-model="filters.start_date"
                        @change="applyFilters"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    />
                </div>
                <div>
                    <label for="end-date" class="block text-sm font-medium text-gray-700 mb-1">
                        Конец
                    </label>
                    <input
                        type="date"
                        id="end-date"
                        v-model="filters.end_date"
                        @change="applyFilters"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    />
                </div>
                <div class="flex items-end">
                    <button
                        @click="exportData"
                        class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
                    >
                        Экспорт данных
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100">
                        <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Всего кейсов</p>
                        <p class="text-2xl font-bold text-gray-900">{{ statistics.total_cases }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Активные кейсы</p>
                        <p class="text-2xl font-bold text-gray-900">{{ statistics.active_cases }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-100">
                        <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Завершенные</p>
                        <p class="text-2xl font-bold text-gray-900">{{ statistics.completed_cases }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-yellow-100">
                        <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Всего команд</p>
                        <p class="text-2xl font-bold text-gray-900">{{ statistics.total_teams }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Популярность кейсов -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Популярность кейсов (по заявкам)</h3>
                <div v-if="chartData.case_popularity" class="h-80">
                    <Bar 
                        :data="chartData.case_popularity" 
                        :options="barChartOptions" 
                        class="w-full h-full"
                    />
                </div>
                <div v-else class="text-center py-12 text-gray-500">
                    Нет данных для отображения
                </div>
            </div>

            <!-- Динамика создания кейсов -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Динамика создания кейсов</h3>
                <div v-if="chartData.case_creation_timeline" class="h-80">
                    <Line 
                        :data="chartData.case_creation_timeline" 
                        :options="lineChartOptions" 
                        class="w-full h-full"
                    />
                </div>
                <div v-else class="text-center py-12 text-gray-500">
                    Нет данных для отображения
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Конверсия заявок в команды -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Конверсия заявок в команды</h3>
                <div v-if="chartData.application_conversion" class="h-80">
                    <Pie 
                        :data="chartData.application_conversion" 
                        :options="pieChartOptions" 
                        class="w-full h-full"
                    />
                </div>
                <div v-else class="text-center py-12 text-gray-500">
                    Нет данных для отображения
                </div>
            </div>

            <!-- Статус кейсов -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Статус кейсов</h3>
                <div v-if="chartData.case_status_distribution" class="h-80">
                    <Doughnut 
                        :data="chartData.case_status_distribution" 
                        :options="doughnutChartOptions" 
                        class="w-full h-full"
                    />
                </div>
                <div v-else class="text-center py-12 text-gray-500">
                    Нет данных для отображения
                </div>
            </div>
        </div>

        <!-- Топ кейсов -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Топ кейсов</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Название
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Заявки
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Команды
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Конверсия
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="topCase in topCases" :key="topCase.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ topCase.title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ topCase.applications_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ topCase.teams_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ topCase.conversion_rate }}%
                            </td>
                        </tr>
                        <tr v-if="topCases.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                Нет данных для отображения
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PartnerLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import PartnerLayout from '@/Layouts/PartnerLayout.vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';
import { Bar, Line, Pie, Doughnut } from 'vue-chartjs';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    statistics: {
        type: Object,
        required: true
    },
    chartData: {
        type: Object,
        required: true
    },
    topCases: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({ period: '30' })
    }
});

const filters = reactive({
    period: props.filters.period || '30',
    start_date: '',
    end_date: ''
});

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    }
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    }
};

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

const applyFilters = () => {
    const params = {
        period: filters.period || undefined,
        start_date: filters.start_date || undefined,
        end_date: filters.end_date || undefined
    };

    router.get(route('partner.analytics.index'), params, {
        preserveState: true,
        replace: true
    });
};

const exportData = () => {
    // Generate CSV export of the data
    const csvContent = generateCSV();
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `analytics_${new Date().toISOString().slice(0, 10)}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const generateCSV = () => {
    // Create CSV content with the analytics data
    let csv = 'Аналитика партнера\n';
    csv += `Период: ${filters.period} дней\n\n`;
    
    csv += 'Статистика\n';
    csv += `Всего кейсов,${props.statistics.total_cases}\n`;
    csv += `Активные кейсы,${props.statistics.active_cases}\n`;
    csv += `Завершенные кейсы,${props.statistics.completed_cases}\n`;
    csv += `Всего команд,${props.statistics.total_teams}\n`;
    csv += `Средняя конверсия,${props.statistics.average_conversion}%\n\n`;
    
    csv += 'Топ кейсов\n';
    csv += 'Название,Заявки,Команды,Конверсия\n';
    props.topCases.forEach(topCase => {
        csv += `"${topCase.title}",${topCase.applications_count},${topCase.teams_count},${topCase.conversion_rate}%\n`;
    });
    
    return csv;
};
</script>