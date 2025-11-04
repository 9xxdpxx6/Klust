<template>
  <div class="table-wrapper">
    <DataTable
      :value="data"
      :paginator="pagination"
      :rows="rowsPerPage"
      :totalRecords="totalRecords"
      :lazy="lazy"
      :loading="loading"
      :sortField="sortField"
      :sortOrder="sortOrder"
      :filters="filters"
      :rowsPerPageOptions="[10, 25, 50, 100]"
      paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
      currentPageReportTemplate="{first} - {last} из {totalRecords}"
      :emptyMessage="emptyMessage"
      @page="handlePage"
      @sort="handleSort"
      @filter="handleFilter"
    >
      <template #header>
        <slot name="header" />
      </template>
      
      <template v-for="(column, index) in columns" :key="index" #[column.slot]="slotProps" v-if="column.slot">
        <slot :name="column.slot" :data="slotProps.data" :index="slotProps.index" />
      </template>
      
      <Column
        v-for="(column, index) in columns"
        :key="index"
        :field="column.field"
        :header="column.header"
        :sortable="column.sortable !== false"
        :filter="column.filter"
        :filterPlaceholder="column.filterPlaceholder"
        :style="column.style"
        :class="column.class"
      >
        <template #body="slotProps" v-if="column.body">
          <component :is="column.body" :data="slotProps.data" :value="slotProps.data[column.field]" />
        </template>
        <template #body="slotProps" v-else-if="column.slot">
          <!-- Slot content handled above -->
        </template>
      </Column>
      
      <template #empty>
        <div class="text-center py-8 text-text-muted">
          {{ emptyMessage }}
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
  columns: {
    type: Array,
    required: true,
  },
  pagination: {
    type: Boolean,
    default: true,
  },
  rowsPerPage: {
    type: Number,
    default: 10,
  },
  totalRecords: {
    type: Number,
    default: null,
  },
  lazy: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  sortField: {
    type: String,
    default: null,
  },
  sortOrder: {
    type: Number,
    default: null,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  emptyMessage: {
    type: String,
    default: 'Нет данных для отображения',
  },
  paginationUrl: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['page-change', 'sort-change', 'filter-change']);

const handlePage = (event) => {
  emit('page-change', event);
  if (props.paginationUrl && props.lazy) {
    router.visit(props.paginationUrl, {
      data: { page: event.page + 1 },
      preserveState: true,
      preserveScroll: true,
    });
  }
};

const handleSort = (event) => {
  emit('sort-change', event);
};

const handleFilter = (event) => {
  emit('filter-change', event);
};
</script>

<style scoped>
:deep(.p-datatable) {
  @apply border border-border rounded-lg overflow-hidden;
}

:deep(.p-datatable-header) {
  @apply bg-surface px-4 py-3;
}

:deep(.p-datatable-thead > tr > th) {
  @apply bg-surface text-text-primary font-semibold border-b border-border;
}

:deep(.p-datatable-tbody > tr) {
  @apply border-b border-border;
}

:deep(.p-datatable-tbody > tr:hover) {
  @apply bg-surface;
}

:deep(.p-paginator) {
  @apply border-t border-border bg-surface;
}
</style>


