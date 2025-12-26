<template>
    <div v-if="links.length > 3" class="flex items-center justify-center">
        <!-- Мобильная версия: стрелки + несколько страниц -->
        <div class="flex items-center gap-1 md:hidden overflow-x-auto pb-2">
            <!-- Кнопка "Назад" (только стрелка) -->
            <Link
                v-if="previousLink && previousLink.url"
                :href="previousLink.url"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center min-w-[40px] flex-shrink-0"
            >
                «
            </Link>
            <span
                v-else
                class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed flex items-center justify-center min-w-[40px] flex-shrink-0"
            >
                «
            </span>

            <!-- Первая страница (если не в видимом диапазоне) -->
            <template v-if="firstPageLink && !isFirstPageVisible">
                <Link
                    v-if="firstPageLink.url"
                    :href="firstPageLink.url"
                    class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg transition-colors min-w-[40px] text-center bg-white text-gray-700 hover:bg-gray-50 flex-shrink-0"
                    v-html="firstPageLink.label"
                />
                <span
                    v-if="visibleMobilePages.length > 0"
                    class="px-2 text-gray-400 flex-shrink-0"
                >
                    ...
                </span>
            </template>

            <!-- Страницы вокруг текущей -->
            <template v-for="(link, index) in visibleMobilePages" :key="`mobile-page-${index}-${link.label}`">
                <div
                    v-if="link.url === null"
                    class="px-3 py-2 text-sm font-medium text-gray-400 border border-gray-300 rounded-lg cursor-not-allowed flex-shrink-0"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg transition-colors min-w-[40px] text-center flex-shrink-0"
                    :class="{
                        'bg-primary text-white border-primary hover:bg-primary-dark': link.active,
                        'bg-white text-gray-700 hover:bg-gray-50': !link.active
                    }"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>

            <!-- Последняя страница (если не в видимом диапазоне) -->
            <template v-if="lastPageLink && !isLastPageVisible">
                <span
                    v-if="visibleMobilePages.length > 0"
                    class="px-2 text-gray-400 flex-shrink-0"
                >
                    ...
                </span>
                <Link
                    v-if="lastPageLink.url"
                    :href="lastPageLink.url"
                    class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg transition-colors min-w-[40px] text-center bg-white text-gray-700 hover:bg-gray-50 flex-shrink-0"
                    v-html="lastPageLink.label"
                />
            </template>

            <!-- Кнопка "Вперед" (только стрелка) -->
            <Link
                v-if="nextLink && nextLink.url"
                :href="nextLink.url"
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center min-w-[40px] flex-shrink-0"
            >
                »
            </Link>
            <span
                v-else
                class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed flex items-center justify-center min-w-[40px] flex-shrink-0"
            >
                »
            </span>
        </div>

        <!-- Десктопная версия: все страницы -->
        <div class="hidden md:flex md:flex-wrap md:-mb-1 md:items-center md:justify-center">
            <template v-for="(link, index) in links" :key="`paginator-${index}-${link.label}`">
                <div
                    v-if="link.url === null"
                    class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-400 border border-gray-300 rounded-lg cursor-not-allowed"
                    v-html="link.label"
                />
                <Link
                    v-else
                    class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border border-gray-300 rounded-lg hover:bg-gray-50 focus:border-primary focus:text-primary transition-colors"
                    :class="{
                        'bg-primary text-white border-primary hover:bg-primary-dark': link.active,
                        'bg-white text-gray-700': !link.active
                    }"
                    :href="link.url"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    links: {
        type: Array,
        required: true,
        default: () => []
    }
})

// Находим ссылки для мобильной версии
// Previous - это первая ссылка в массиве (обычно "« Назад")
const previousLink = computed(() => {
    if (props.links.length > 0) {
        const firstLink = props.links[0]
        // Проверяем, что это действительно ссылка "Previous"
        const label = firstLink.label?.toLowerCase() || ''
        if (label.includes('назад') || label.includes('previous') || label.includes('&laquo;') || label.includes('«')) {
            return firstLink
        }
    }
    return null
})

// Next - это последняя ссылка в массиве (обычно "Вперед »")
const nextLink = computed(() => {
    if (props.links.length > 0) {
        const lastLink = props.links[props.links.length - 1]
        // Проверяем, что это действительно ссылка "Next"
        const label = lastLink.label?.toLowerCase() || ''
        if (label.includes('вперед') || label.includes('next') || label.includes('&raquo;') || label.includes('»')) {
            return lastLink
        }
    }
    return null
})

// Проверяем, является ли ссылка номером страницы (а не "Previous"/"Next")
const isPageNumber = (link) => {
    if (!link || !link.label) return false
    // Убираем HTML-сущности и символы стрелок
    const cleanLabel = link.label
        .replace(/&laquo;|&raquo;|«|»/g, '')
        .replace(/<[^>]*>/g, '')
        .trim()
        .toLowerCase()
    
    // Проверяем, не содержит ли это слова "назад", "вперед", "previous", "next"
    const isNavigation = ['назад', 'вперед', 'previous', 'next'].some(word => 
        cleanLabel.includes(word)
    )
    
    if (isNavigation) return false
    
    // Проверяем, является ли это числом
    return !isNaN(parseInt(cleanLabel))
}

// Получаем первую и последнюю страницы
const firstPageLink = computed(() => {
    const pageLinks = props.links.filter(link => isPageNumber(link))
    return pageLinks.length > 0 ? pageLinks[0] : null
})

const lastPageLink = computed(() => {
    const pageLinks = props.links.filter(link => isPageNumber(link))
    return pageLinks.length > 0 ? pageLinks[pageLinks.length - 1] : null
})

// Видимые страницы для мобильной версии (адаптивно: больше страниц на больших экранах)
const visibleMobilePages = computed(() => {
    const currentIndex = props.links.findIndex(link => link.active)
    if (currentIndex === -1) return []
    
    // Фильтруем только ссылки с номерами страниц
    const pageLinks = props.links.filter(link => isPageNumber(link))
    
    const currentPageIndex = pageLinks.findIndex(link => link.active)
    if (currentPageIndex === -1) return []
    
    // Адаптивное количество страниц:
    // На очень маленьких экранах (< 375px): по 1 странице с каждой стороны (всего 3)
    // На маленьких экранах (375-640px): по 2 страницы с каждой стороны (всего 5)
    // На средних экранах (640-768px): по 3 страницы с каждой стороны (всего 7)
    // Используем CSS классы для адаптивности, но в JS вычисляем максимум
    
    // По умолчанию показываем по 3 страницы с каждой стороны (всего 7)
    // На маленьких экранах CSS скроет лишние через медиа-запросы
    const pagesOnSide = 3
    const start = Math.max(0, currentPageIndex - pagesOnSide)
    const end = Math.min(pageLinks.length, currentPageIndex + pagesOnSide + 1)
    
    return pageLinks.slice(start, end)
})

// Проверяем, видна ли первая страница в текущем диапазоне
const isFirstPageVisible = computed(() => {
    if (!firstPageLink.value || visibleMobilePages.value.length === 0) return false
    const firstVisible = visibleMobilePages.value[0]
    return firstPageLink.value.label === firstVisible.label
})

// Проверяем, видна ли последняя страница в текущем диапазоне
const isLastPageVisible = computed(() => {
    if (!lastPageLink.value || visibleMobilePages.value.length === 0) return false
    const lastVisible = visibleMobilePages.value[visibleMobilePages.value.length - 1]
    return lastPageLink.value.label === lastVisible.label
})
</script>
