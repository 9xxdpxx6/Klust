<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-text-primary mb-2">Пользователи</h1>
                    <p class="text-text-secondary">Управление всеми пользователями системы</p>
                </div>
                <Button
                    variant="primary"
                    icon="pi pi-plus"
                    @click="safeVisit('admin.users.create')"
                >
                    Создать пользователя
                </Button>
            </div>

            <FlashMessage />

            <!-- Фильтры -->
            <Card>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Select
                        v-model="filters.role"
                        :options="roleOptions"
                        optionLabel="label"
                        optionValue="value"
                        label="Роль"
                        placeholder="Все роли"
                        @update:modelValue="handleFilter"
                    />
                    <Select
                        v-model="filters.status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        label="Статус"
                        placeholder="Все статусы"
                        @update:modelValue="handleFilter"
                    />
                    <Input
                        v-model="filters.search"
                        label="Поиск"
                        placeholder="Имя, email, kubgtu_id..."
                        rightIcon="pi pi-search"
                        @update:modelValue="handleSearch"
                    />
                </div>
            </Card>

            <!-- Таблица -->
            <Card>
                <DataTable
                    :value="users.data"
                    :paginator="true"
                    :rows="users.meta?.per_page || 10"
                    :totalRecords="users.meta?.total || 0"
                    :lazy="true"
                    :loading="loading"
                    @page="handlePage"
                >
                    <Column field="id" header="ID" :sortable="true" style="width: 80px" />
                    <Column field="name" header="ФИО" :sortable="true" />
                    <Column field="email" header="Email" :sortable="true" />
                    <Column field="kubgtu_id" header="ID КубГТУ" :sortable="true">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.kubgtu_id">{{ slotProps.data.kubgtu_id }}</span>
                            <span v-else class="text-text-muted">—</span>
                        </template>
                    </Column>
                    <Column field="roles" header="Роль">
                        <template #body="slotProps">
                            <Badge
                                v-for="role in slotProps.data.roles"
                                :key="role"
                                variant="secondary"
                                size="sm"
                                class="mr-1"
                            >
                                {{ role }}
                            </Badge>
                        </template>
                    </Column>
                    <Column field="course" header="Курс">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.profile?.course">{{ slotProps.data.profile.course }}</span>
                            <span v-else class="text-text-muted">—</span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Дата регистрации" :sortable="true">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.created_at) }}
                        </template>
                    </Column>
                    <Column header="Действия" style="width: 150px">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    icon="pi pi-eye"
                                    @click="safeVisit('admin.users.show', slotProps.data.id)"
                                />
                                <Button
                                    variant="outline"
                                    size="sm"
                                    icon="pi pi-pencil"
                                    @click="safeVisit('admin.users.edit', slotProps.data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FlashMessage from '@/Components/Shared/FlashMessage.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Badge from '@/Components/UI/Badge.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { routeExists } from '@/Utils/routes';

const props = defineProps({
    users: {
        type: Object,
        default: () => ({ data: [], meta: {}, links: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const filters = ref({
    role: props.filters.role || '',
    status: props.filters.status || '',
    search: props.filters.search || '',
});

const roleOptions = [
    { label: 'Все роли', value: '' },
    { label: 'Студент', value: 'student' },
    { label: 'Партнер', value: 'partner' },
    { label: 'Преподаватель', value: 'teacher' },
    { label: 'Администратор', value: 'admin' },
];

const statusOptions = [
    { label: 'Все статусы', value: '' },
    { label: 'Активен', value: 'active' },
    { label: 'Заблокирован', value: 'blocked' },
];

const handleFilter = () => {
    if (routeExists('admin.users.index')) {
        router.get(route('admin.users.index'), filters.value, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

let searchTimeout = null;
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        handleFilter();
    }, 500);
};

const handlePage = (event) => {
    if (routeExists('admin.users.index')) {
        router.get(route('admin.users.index'), {
            ...filters.value,
            page: event.page + 1,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const safeVisit = (routeName, params = {}) => {
    if (routeExists(routeName)) {
        try {
            router.visit(route(routeName, params));
        } catch (e) {
            console.warn(`Route "${routeName}" not found`);
        }
    }
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU');
};
</script>

