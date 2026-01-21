<template>
  <aside
    :class="[
      'base-sidebar',
      {
        'collapsed': isCollapsed && !isMobile,
        'mobile-hidden': !isOpen && isMobile,
      }
    ]"
  >
    <div class="base-sidebar__header">
      <slot name="header">
        <div class="flex items-center justify-between">
          <div v-if="!isCollapsed || isMobile" class="flex items-center gap-3">
            <img 
              :src="kubstuIconWhite" 
              alt="КубГТУ" 
              class="base-sidebar__header-icon"
            />
            <h2 class="text-lg font-bold text-white">
              {{ title }}
            </h2>
          </div>
          <button
            v-if="showToggle"
            @click="toggleCollapse"
            class="p-1 rounded hover:bg-white/10 transition-colors text-white"
            aria-label="Свернуть/развернуть меню"
          >
            <i :class="isCollapsed ? 'pi pi-angle-right' : 'pi pi-angle-left'"></i>
          </button>
        </div>
      </slot>
    </div>
    
    <nav class="base-sidebar__nav">
      <div v-for="(item, index) in items" :key="index">
        <Link
          v-if="!item.children && routeExists(item.route)"
          :href="getRouteUrl(item.route)"
          :class="[
            'base-sidebar__item',
            { 'active': isActive(item.route) }
          ]"
        >
          <i :class="['base-sidebar__item-icon', item.icon]"></i>
          <span v-if="!isCollapsed || isMobile" class="base-sidebar__item-label">
            {{ item.label }}
          </span>
        </Link>
        
        <!-- Группа с подпунктами (если нужно позже) -->
        <div v-else class="base-sidebar__group">
          <div class="base-sidebar__item base-sidebar__item--group">
            <i :class="['base-sidebar__item-icon', item.icon]"></i>
            <span v-if="!isCollapsed || isMobile" class="base-sidebar__item-label">
              {{ item.label }}
            </span>
          </div>
          <div v-if="!isCollapsed || isMobile" class="base-sidebar__subitems">
            <Link
              v-for="(child, childIndex) in item.children.filter(c => routeExists(c.route))"
              :key="childIndex"
              :href="getRouteUrl(child.route)"
              :class="[
                'base-sidebar__item base-sidebar__item--child',
                { 'active': isActive(child.route) }
              ]"
            >
              <i :class="['base-sidebar__item-icon', child.icon || 'pi pi-circle-fill']"></i>
              <span class="base-sidebar__item-label">{{ child.label }}</span>
            </Link>
          </div>
        </div>
      </div>
    </nav>
    
    <div class="base-sidebar__footer">
      <slot name="footer">
        <div v-if="!isCollapsed || isMobile" class="flex justify-center items-center">
          <img 
            :src="kubgtuSquareLogoWhite" 
            alt="КубГТУ" 
            class="base-sidebar__logo"
          />
        </div>
      </slot>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useNavigation } from '@/Composables/useNavigation';
import { useSidebar } from '@/Composables/useSidebar';
import { routeExists, getRouteUrl } from '@/Utils/routes';

// Статические файлы из public/ доступны напрямую через абсолютные пути
const kubstuIconWhite = '/images/assets/kubstu-icon-white.png';
const kubgtuSquareLogoWhite = '/images/assets/kubgtu-square-logo-white.png';

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Меню',
  },
  initialCollapsed: {
    type: Boolean,
    default: false,
  },
  isCollapsed: {
    type: Boolean,
    default: null,
  },
  isMobile: {
    type: Boolean,
    default: null,
  },
  showToggle: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['toggle-collapse']);

const { isActive } = useNavigation();

// Используем переданные props или создаем локальное состояние
const localSidebar = props.isCollapsed !== null ? null : useSidebar(props.initialCollapsed);
const isCollapsed = computed(() => props.isCollapsed !== null ? props.isCollapsed : localSidebar?.isCollapsed.value ?? false);
const isMobile = computed(() => props.isMobile !== null ? props.isMobile : localSidebar?.isMobile.value ?? false);
const isOpen = computed(() => {
  if (isMobile.value) {
    return localSidebar?.isMobileOpen.value ?? false;
  }
  return !isCollapsed.value;
});

const toggleCollapse = () => {
  if (props.isCollapsed !== null) {
    // Если состояние управляется извне, эмитим событие
    emit('toggle-collapse');
  } else {
    // Иначе используем локальное состояние
    localSidebar?.toggleCollapse();
  }
};
</script>

<style scoped>
@import '@/Styles/layout.css';

.base-sidebar__group {
  margin-bottom: 0.5rem;
}

.base-sidebar__item--group {
  font-weight: 600;
  color: var(--color-kubgtu-white);
  cursor: default;
  opacity: 0.9;
}

.base-sidebar__item--group:hover {
  background: transparent;
  opacity: 1;
}

.base-sidebar__subitems {
  padding-left: 1rem;
  margin-top: 0.25rem;
}

.base-sidebar__item--child {
  padding-left: 3rem;
  font-size: 0.875rem;
}

.base-sidebar__item--child .base-sidebar__item-icon {
  font-size: 0.5rem;
}

.base-sidebar__footer {
  padding: .5rem .5rem;
  margin-top: auto;
  flex-shrink: 0;
}

.base-sidebar__logo {
  max-width: 100%;
  height: auto;
  opacity: 0.3;
  transition: opacity 0.2s;
}

.base-sidebar__logo:hover {
  opacity: 1;
}

.base-sidebar__header-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
}
</style>

