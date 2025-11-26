<template>
  <div class="icon-picker">
    <div class="mb-4">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Поиск иконки..."
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
      />
    </div>

    <div :class="[
      'border rounded-md max-h-96 overflow-y-auto',
      error ? 'border-red-500' : 'border-gray-300'
    ]">
      <div class="grid grid-cols-8 gap-2 p-4">
        <button
          v-for="icon in filteredIcons"
          :key="icon"
          type="button"
          @click="selectIcon(icon)"
          :class="[
            'p-3 border rounded-md transition-colors hover:bg-indigo-50 hover:border-indigo-500 flex items-center justify-center',
            selectedIcon === icon ? 'bg-indigo-100 border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-300'
          ]"
          :title="icon"
        >
          <i :class="`pi ${icon} text-2xl text-gray-700`"></i>
        </button>
      </div>
    </div>

    <div v-if="selectedIcon" class="mt-4 p-3 bg-gray-50 rounded-md">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 flex items-center justify-center bg-white border border-gray-300 rounded">
          <i :class="`pi ${selectedIcon} text-3xl text-gray-700`"></i>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-700">Выбранная иконка:</p>
          <p class="text-xs text-gray-500 font-mono">{{ selectedIcon }}</p>
        </div>
        <button
          v-if="selectedIcon"
          type="button"
          @click="clearSelection"
          class="px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors"
        >
          Очистить
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
  error: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const selectedIcon = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

// Comprehensive list of PrimeIcons
const allIcons = [
  'pi-align-center',
  'pi-align-justify',
  'pi-align-left',
  'pi-align-right',
  'pi-amazon',
  'pi-android',
  'pi-angle-down',
  'pi-angle-left',
  'pi-angle-right',
  'pi-angle-up',
  'pi-apple',
  'pi-arrow-down',
  'pi-arrow-left',
  'pi-arrow-right',
  'pi-arrow-up',
  'pi-arrow-circle-down',
  'pi-arrow-circle-left',
  'pi-arrow-circle-right',
  'pi-arrow-circle-up',
  'pi-asterisk',
  'pi-backward',
  'pi-ban',
  'pi-bars',
  'pi-bell',
  'pi-bolt',
  'pi-book',
  'pi-bookmark',
  'pi-bookmark-fill',
  'pi-box',
  'pi-briefcase',
  'pi-building',
  'pi-calendar',
  'pi-calendar-minus',
  'pi-calendar-plus',
  'pi-calendar-times',
  'pi-camera',
  'pi-car',
  'pi-caret-down',
  'pi-caret-left',
  'pi-caret-right',
  'pi-caret-up',
  'pi-chart-bar',
  'pi-chart-line',
  'pi-chart-pie',
  'pi-check',
  'pi-check-circle',
  'pi-check-square',
  'pi-chevron-circle-down',
  'pi-chevron-circle-left',
  'pi-chevron-circle-right',
  'pi-chevron-circle-up',
  'pi-chevron-down',
  'pi-chevron-left',
  'pi-chevron-right',
  'pi-chevron-up',
  'pi-circle',
  'pi-circle-fill',
  'pi-clock',
  'pi-cloud',
  'pi-cloud-download',
  'pi-cloud-upload',
  'pi-code',
  'pi-cog',
  'pi-comment',
  'pi-comments',
  'pi-compass',
  'pi-copy',
  'pi-credit-card',
  'pi-database',
  'pi-delete',
  'pi-desktop',
  'pi-directions',
  'pi-directions-alt',
  'pi-discord',
  'pi-dollar',
  'pi-download',
  'pi-eject',
  'pi-ellipsis-h',
  'pi-ellipsis-v',
  'pi-envelope',
  'pi-euro',
  'pi-exclamation-circle',
  'pi-exclamation-triangle',
  'pi-external-link',
  'pi-eye',
  'pi-eye-slash',
  'pi-facebook',
  'pi-fast-backward',
  'pi-fast-forward',
  'pi-file',
  'pi-file-edit',
  'pi-file-excel',
  'pi-file-pdf',
  'pi-file-word',
  'pi-filter',
  'pi-filter-fill',
  'pi-filter-slash',
  'pi-flag',
  'pi-flag-fill',
  'pi-folder',
  'pi-folder-open',
  'pi-forward',
  'pi-gift',
  'pi-github',
  'pi-globe',
  'pi-google',
  'pi-hashtag',
  'pi-heart',
  'pi-heart-fill',
  'pi-home',
  'pi-id-card',
  'pi-image',
  'pi-images',
  'pi-inbox',
  'pi-info',
  'pi-info-circle',
  'pi-instagram',
  'pi-key',
  'pi-language',
  'pi-link',
  'pi-linkedin',
  'pi-list',
  'pi-lock',
  'pi-lock-open',
  'pi-map',
  'pi-map-marker',
  'pi-megaphone',
  'pi-microphone',
  'pi-microsoft',
  'pi-minus',
  'pi-minus-circle',
  'pi-mobile',
  'pi-money-bill',
  'pi-moon',
  'pi-palette',
  'pi-paperclip',
  'pi-pause',
  'pi-paypal',
  'pi-pencil',
  'pi-percentage',
  'pi-phone',
  'pi-play',
  'pi-plus',
  'pi-plus-circle',
  'pi-power-off',
  'pi-print',
  'pi-qrcode',
  'pi-question',
  'pi-question-circle',
  'pi-radiobutton',
  'pi-refresh',
  'pi-replay',
  'pi-reply',
  'pi-save',
  'pi-search',
  'pi-search-minus',
  'pi-search-plus',
  'pi-send',
  'pi-server',
  'pi-share-alt',
  'pi-shield',
  'pi-shopping-bag',
  'pi-shopping-cart',
  'pi-sign-in',
  'pi-sign-out',
  'pi-sitemap',
  'pi-slack',
  'pi-sliders-h',
  'pi-sliders-v',
  'pi-sort',
  'pi-sort-alpha-down',
  'pi-sort-alpha-up',
  'pi-sort-alt',
  'pi-sort-amount-down',
  'pi-sort-amount-up',
  'pi-sort-down',
  'pi-sort-numeric-down',
  'pi-sort-numeric-up',
  'pi-sort-up',
  'pi-spinner',
  'pi-star',
  'pi-star-fill',
  'pi-step-backward',
  'pi-step-forward',
  'pi-stop',
  'pi-stop-circle',
  'pi-sun',
  'pi-sync',
  'pi-table',
  'pi-tablet',
  'pi-tag',
  'pi-tags',
  'pi-telegram',
  'pi-th-large',
  'pi-thumbs-down',
  'pi-thumbs-down-fill',
  'pi-thumbs-up',
  'pi-thumbs-up-fill',
  'pi-ticket',
  'pi-times',
  'pi-times-circle',
  'pi-trash',
  'pi-twitter',
  'pi-undo',
  'pi-unlock',
  'pi-upload',
  'pi-user',
  'pi-user-edit',
  'pi-user-minus',
  'pi-user-plus',
  'pi-users',
  'pi-video',
  'pi-vimeo',
  'pi-volume-down',
  'pi-volume-off',
  'pi-volume-up',
  'pi-wallet',
  'pi-whatsapp',
  'pi-wifi',
  'pi-window-maximize',
  'pi-window-minimize',
  'pi-wrench',
  'pi-youtube',
  'pi-zoom-in',
  'pi-zoom-out',
  'pi-trophy',
  'pi-trophy',
  'pi-lightbulb',
  'pi-cog',
  'pi-chart-bar',
  'pi-chart-line',
  'pi-chart-pie',
];

const filteredIcons = computed(() => {
  if (!searchQuery.value) {
    return allIcons;
  }
  const query = searchQuery.value.toLowerCase();
  return allIcons.filter(icon => 
    icon.toLowerCase().includes(query) || 
    icon.replace('pi-', '').replace(/-/g, ' ').includes(query)
  );
});

const selectIcon = (icon) => {
  selectedIcon.value = icon;
};

const clearSelection = () => {
  selectedIcon.value = null;
};
</script>

<style scoped>
.icon-picker {
  width: 100%;
}
</style>

