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
          <h2 v-if="!isCollapsed || isMobile" class="text-lg font-bold text-primary">
            {{ title }}
          </h2>
          <button
            v-if="showToggle"
            @click="toggleCollapse"
            class="p-1 rounded hover:bg-surface-hover transition-colors"
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
      <slot name="footer" />
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useNavigation } from '@/Composables/useNavigation';
import { useSidebar } from '@/Composables/useSidebar';
import { routeExists, getRouteUrl } from '@/Utils/routes';

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
  showToggle: {
    type: Boolean,
    default: true,
  },
});

const { isActive } = useNavigation();
const { isCollapsed, isMobile, isOpen, toggleCollapse } = useSidebar(props.initialCollapsed);
</script>

<style scoped>
@import '@/Styles/layout.css';

.base-sidebar__group {
  margin-bottom: 0.5rem;
}

.base-sidebar__item--group {
  font-weight: 600;
  color: var(--color-text-primary);
  cursor: default;
}

.base-sidebar__item--group:hover {
  background: transparent;
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
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-light);
  margin-top: auto;
}
</style>

