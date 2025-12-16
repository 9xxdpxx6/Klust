<template>
    <div class="space-y-6">
        <Head title="Аналитика" />
        <h1 class="text-2xl font-bold text-gray-900">Аналитика</h1>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <Select
                        v-model="filters.period"
                        label="Период"
                        :options="periodOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Выберите период"
                        @update:modelValue="onPeriodChange"
                    />
                </div>
                <div>
                    <DatePicker
                        v-model="filters.date_from"
                        label="Начало"
                        placeholder="Выберите дату"
                        @update:modelValue="applyFilters"
                    />
                </div>
                <div>
                    <DatePicker
                        v-model="filters.date_to"
                        label="Конец"
                        placeholder="Выберите дату"
                        @update:modelValue="applyFilters"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-transparent">
                        &nbsp;
                    </label>
                    <div class="relative w-full">
                        <button
                            @click="showExportMenu = !showExportMenu"
                            class="w-full h-10 bg-gray-600 hover:bg-gray-700 text-white px-4 rounded-md text-sm font-medium transition duration-150 ease-in-out flex items-center justify-center gap-2"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Экспорт данных
                        </button>
                        <div
                            v-if="showExportMenu"
                            @click.stop
                            class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                        >
                            <div class="py-1">
                                <a
                                    :href="getExportUrl('partner.analytics.export.cases')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Экспорт кейсов (Excel)
                                </a>
                                <a
                                    :href="getExportUrl('partner.analytics.export.applications')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Экспорт заявок (Excel)
                                </a>
                                <a
                                    :href="getExportUrl('partner.analytics.export.teams')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                >
                                    Экспорт команд (Excel)
                                </a>
                            </div>
                        </div>
                    </div>
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
            <!-- Распределение кейсов по навыкам -->
            <div class="bg-white p-4 rounded-lg shadow border">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Распределение кейсов по навыкам</h3>
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
                                №
                            </th>
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
                                Процент одобрения
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(topCase, index) in topCases" :key="topCase.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ index + 1 }}
                            </td>
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
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                Нет данных для отображения
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import DatePicker from '@/Components/UI/DatePicker.vue';
import Select from '@/Components/UI/Select.vue';
import { parseLocalDate, formatDateForServer } from '@/Composables/useDateHelper';
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
    analytics: {
        type: Object,
        default: () => ({
            overview: {},
            application_stats: {},
            charts: {},
            date_range: {}
        })
    },
    filters: {
        type: Object,
        default: () => ({ period: '30' })
    }
});

// Initialize filters with period-based dates if dates are not provided
const initializeFilters = () => {
    const period = props.filters.period || '30';
    let dateFrom = parseLocalDate(props.filters.date_from);
    let dateTo = parseLocalDate(props.filters.date_to);
    
    // If period is set but dates are not, calculate dates from period
    if (period && period !== 'all' && !dateFrom && !dateTo) {
        const days = parseInt(period);
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - days);
        
        dateFrom = startDate;
        dateTo = endDate;
    }
    
    return {
        period,
        date_from: dateFrom,
        date_to: dateTo
    };
};

const filters = reactive(initializeFilters());

// Extract data from analytics object
const statistics = computed(() => props.analytics?.overview || {});
const topCases = computed(() => props.analytics?.top_cases || []);

// Transform chart data to Chart.js format
const chartData = computed(() => {
    const charts = props.analytics?.charts || {};
    
    return {
        // Applications over time (Line chart)
        case_creation_timeline: charts.applications_over_time ? {
            labels: charts.applications_over_time.labels || [],
            datasets: [{
                label: 'Заявки',
                data: charts.applications_over_time.data || [],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        } : null,
        
        // Cases by status (Doughnut chart)
        case_status_distribution: charts.cases_by_status ? {
            labels: charts.cases_by_status.labels || [],
            datasets: [{
                data: charts.cases_by_status.data || [],
                backgroundColor: [
                    'rgb(34, 197, 94)',  // Active - green
                    'rgb(59, 130, 246)', // Completed - blue
                    'rgb(156, 163, 175)', // Draft - gray
                    'rgb(168, 85, 247)'  // Archived - purple
                ]
            }]
        } : null,
        
        // Application conversion (Pie chart)
        application_conversion: charts.application_conversion ? {
            labels: charts.application_conversion.labels || [],
            datasets: [{
                data: charts.application_conversion.data || [],
                backgroundColor: [
                    'rgb(234, 179, 8)',  // Pending - yellow
                    'rgb(34, 197, 94)',   // Accepted - green
                    'rgb(239, 68, 68)'   // Rejected - red
                ]
            }]
        } : null,
        
        // Popular skills (Bar chart)
        case_popularity: charts.popular_skills ? {
            labels: charts.popular_skills.labels || [],
            datasets: [{
                label: 'Количество',
                data: charts.popular_skills.data || [],
                backgroundColor: 'rgb(59, 130, 246)'
            }]
        } : null
    };
});

const periodOptions = computed(() => [
    { label: 'Последние 7 дней', value: '7' },
    { label: 'Последние 30 дней', value: '30' },
    { label: 'Последние 90 дней', value: '90' },
    { label: 'Весь период', value: 'all' },
]);

const showExportMenu = ref(false);

// Close export menu when clicking outside
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            showExportMenu.value = false;
        }
    });
});

// Месяцы на русском для tooltip
const monthNames = {
    1: 'янв', 2: 'фев', 3: 'мар', 4: 'апр', 5: 'май', 6: 'июн',
    7: 'июл', 8: 'авг', 9: 'сен', 10: 'окт', 11: 'ноя', 12: 'дек'
};

// Функция для форматирования даты в tooltip
const formatDateForTooltip = (label) => {
    if (!label) return '';
    
    // Если label уже в формате "сен 2025", возвращаем как есть
    if (typeof label === 'string' && /^[а-яё]{3}\s\d{4}$/i.test(label)) {
        return label;
    }
    
    // Пытаемся распарсить дату из формата "M Y" (например, "Dec 2025" или "Sep 2025")
    const dateMatch = label?.toString().match(/(\w+)\s(\d{4})/);
    if (dateMatch) {
        const monthName = dateMatch[1];
        const year = dateMatch[2];
        
        // Маппинг английских названий месяцев (полные и сокращенные)
        const monthMap = {
            'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6,
            'July': 7, 'August': 8, 'September': 9, 'October': 10, 'November': 11, 'December': 12,
            'Jan': 1, 'Feb': 2, 'Mar': 3, 'Apr': 4, 'May': 5, 'Jun': 6,
            'Jul': 7, 'Aug': 8, 'Sep': 9, 'Oct': 10, 'Nov': 11, 'Dec': 12
        };
        
        const monthNum = monthMap[monthName];
        if (monthNum && monthNames[monthNum]) {
            return `${monthNames[monthNum]} ${year}`;
        }
    }
    
    return label;
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                title: (context) => {
                    if (context && context.length > 0 && context[0].label) {
                        return formatDateForTooltip(context[0].label);
                    }
                    return '';
                },
                label: (context) => {
                    const value = Math.round(context.parsed.y);
                    return `Количество: ${value}`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                precision: 0
            }
        }
    }
};

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                title: (context) => {
                    if (context && context.length > 0) {
                        return formatDateForTooltip(context[0].label);
                    }
                    return '';
                },
                label: (context) => {
                    const label = context.dataset.label || 'Заявки';
                    const value = Math.round(context.parsed.y);
                    return `${label}: ${value}`;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                precision: 0
            }
        }
    }
};

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            callbacks: {
                label: (context) => {
                    const label = context.label || '';
                    const value = Math.round(context.parsed || context.raw);
                    return `${label}: ${value}`;
                }
            }
        }
    }
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 12
                }
            }
        },
        tooltip: {
            callbacks: {
                label: (context) => {
                    const label = context.label || '';
                    const value = context.parsed || context.raw;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} (${percentage}%)`;
                }
            }
        }
    }
};

const onPeriodChange = () => {
    // При изменении периода автоматически устанавливаем даты
    if (filters.period && filters.period !== 'all') {
        const days = parseInt(filters.period);
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - days);
        
        filters.date_from = startDate;
        filters.date_to = endDate;
    } else if (filters.period === 'all') {
        // Для "Весь период" очищаем даты
        filters.date_from = null;
        filters.date_to = null;
    }
    
    applyFilters();
};

const applyFilters = () => {
    const params = {
        period: filters.period || undefined,
        date_from: formatDateForServer(filters.date_from) || undefined,
        date_to: formatDateForServer(filters.date_to) || undefined
    };

    router.get(route('partner.analytics.index'), params, {
        preserveState: true,
        replace: true
    });
};

const getExportUrl = (routeName) => {
    const params = new URLSearchParams();
    
    if (filters.period) {
        params.append('period', filters.period);
    }
    
    if (filters.date_from) {
        const dateFrom = formatDateForServer(filters.date_from);
        if (dateFrom) {
            params.append('date_from', dateFrom);
        }
    }
    
    if (filters.date_to) {
        const dateTo = formatDateForServer(filters.date_to);
        if (dateTo) {
            params.append('date_to', dateTo);
        }
    }
    
    const queryString = params.toString();
    return queryString ? `${route(routeName)}?${queryString}` : route(routeName);
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
    csv += `Всего кейсов,${statistics.value.total_cases || 0}\n`;
    csv += `Активные кейсы,${statistics.value.active_cases || 0}\n`;
    csv += `Завершенные кейсы,${statistics.value.completed_cases || 0}\n`;
    csv += `Всего команд,${statistics.value.total_teams || 0}\n`;
    csv += `Средний процент одобрения,${statistics.value.average_conversion || 0}%\n\n`;
    
    csv += 'Топ кейсов\n';
    csv += 'Название,Заявки,Команды,Процент одобрения\n';
    topCases.value.forEach(topCase => {
        csv += `"${topCase.title}",${topCase.applications_count},${topCase.teams_count},${topCase.conversion_rate}%\n`;
    });
    
    return csv;
};
</script>