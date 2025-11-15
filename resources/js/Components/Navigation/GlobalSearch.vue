<template>
  <div class="relative">
    <IconField>
      <InputIcon>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </InputIcon>
      <InputText
        v-model="searchQuery"
        type="text"
        placeholder="Поиск кейсов, пользователей, навыков..."
        @focus="showResults = true"
        @input="debouncedSearch"
      />
    </IconField>
    
    <div 
      v-if="showResults && searchQuery && results"
      class="absolute z-50 w-full mt-1 bg-white shadow-lg rounded-md overflow-hidden dark:bg-gray-800"
    >
      <div v-if="results.cases.length > 0">
        <div class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          Кейсы
        </div>
        <div 
          v-for="item in results.cases" 
          :key="`case-${item.id}`"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-700 dark:text-white"
          @click="goToCase(item)"
        >
          <div class="font-medium truncate">{{ item.title }}</div>
          <div class="text-sm text-gray-500 truncate">{{ item.description }}</div>
        </div>
      </div>
      
      <div v-if="results.users.length > 0">
        <div class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          Пользователи
        </div>
        <div 
          v-for="item in results.users" 
          :key="`user-${item.id}`"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-700 dark:text-white"
        >
          <div class="font-medium">{{ item.name }}</div>
          <div class="text-sm text-gray-500">{{ item.email }}</div>
        </div>
      </div>
      
      <div v-if="results.skills.length > 0">
        <div class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          Навыки
        </div>
        <div 
          v-for="item in results.skills" 
          :key="`skill-${item.id}`"
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-700 dark:text-white"
        >
          <div class="font-medium">{{ item.name }}</div>
          <div class="text-sm text-gray-500">{{ item.description }}</div>
        </div>
      </div>
      
      <div v-if="!results.cases.length && !results.users.length && !results.skills.length && searchQuery">
        <div class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
          Ничего не найдено по запросу "{{ searchQuery }}"
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';

interface SearchResult {
  cases: Array<{
    id: number;
    title: string;
    description: string;
    url: string;
  }>;
  users: Array<{
    id: number;
    name: string;
    email: string;
  }>;
  skills: Array<{
    id: number;
    name: string;
    description: string;
  }>;
}

const searchQuery = ref('');
const results = ref<SearchResult | null>(null);
const showResults = ref(false);
let searchTimeout: number | null = null;

const debouncedSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  
  if (searchQuery.value.length < 2) {
    results.value = null;
    return;
  }
  
  searchTimeout = setTimeout(() => {
    performSearch();
  }, 300) as unknown as number;
};

const performSearch = async () => {
  try {
    const response = await fetch(`/search?q=${encodeURIComponent(searchQuery.value)}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    
    if (response.ok) {
      results.value = await response.json();
    }
  } catch (error) {
    console.error('Search error:', error);
    results.value = null;
  }
};

const goToCase = (item: any) => {
  router.get(item.url);
  showResults.value = false;
  searchQuery.value = '';
  results.value = null;
};

const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement;
  if (!target.closest('.relative')) {
    showResults.value = false;
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
</script>