<template>
    <div class="flow-root">
        <div v-if="history && history.length > 0" class="-mb-8">
            <div v-for="(item, idx) in history" :key="item.id" class="relative pb-8">
                <!-- Connecting line (not on last item) -->
                <span
                    v-if="idx !== history.length - 1"
                    class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                    aria-hidden="true"
                ></span>

                <div class="relative flex space-x-3">
                    <!-- Status Icon -->
                    <div>
                        <span
                            :class="[
                                getStatusColor(item.new_status?.name),
                                'h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white'
                            ]"
                        >
                            <svg
                                v-if="item.new_status?.name === 'accepted'"
                                class="h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg
                                v-else-if="item.new_status?.name === 'rejected'"
                                class="h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <svg
                                v-else-if="item.new_status?.name === 'pending'"
                                class="h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg
                                v-else
                                class="h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                        <div>
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">{{ item.new_status?.label || item.new_status?.name }}</span>
                                <span v-if="item.old_status" class="text-gray-500">
                                    (из "{{ item.old_status?.label || item.old_status?.name }}")
                                </span>
                            </p>
                            <p v-if="item.comment" class="mt-1 text-sm text-gray-600">
                                {{ item.comment }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ item.changed_by?.name || 'Система' }}
                            </p>
                        </div>
                        <div class="whitespace-nowrap text-right text-sm text-gray-500">
                            <time :datetime="item.changed_at">
                                {{ formatDate(item.changed_at) }}
                            </time>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-sm">История изменений пока пуста</p>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    history: {
        type: Array,
        required: true,
        default: () => []
    }
});

const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-yellow-500',
        'accepted': 'bg-green-500',
        'rejected': 'bg-red-500',
        'completed': 'bg-blue-500',
    };
    return colors[status] || 'bg-gray-500';
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'На рассмотрении',
        'accepted': 'Принята',
        'rejected': 'Отклонена',
        'completed': 'Завершена',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diffMs = now - d;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Только что';
    if (diffMins < 60) return `${diffMins} мин. назад`;
    if (diffHours < 24) return `${diffHours} ч. назад`;
    if (diffDays < 7) return `${diffDays} дн. назад`;

    return d.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
