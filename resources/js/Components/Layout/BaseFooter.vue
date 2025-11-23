<template>
  <footer class="base-footer">
    <div class="base-footer__content">
      <div class="base-footer__section">
        <h3>О проекте</h3>
        <p class="text-sm opacity-80">
          Klust - платформа для практического обучения студентов КубГТУ через решение реальных кейсов от партнеров.
        </p>
      </div>
      
      <div class="base-footer__section">
        <h3>Контакты</h3>
        <ul>
          <li>
            <a href="mailto:info@klust.ru">info@klust.ru</a>
          </li>
          <li>
            <a href="tel:+7(861)219-95-00">+7 (861) 219-95-00</a>
          </li>
        </ul>
      </div>
      
      <div class="base-footer__section">
        <h3>Полезные ссылки</h3>
        <ul>
          <li>
            <a href="#">Помощь</a>
          </li>
          <li>
            <a href="#">Правила использования</a>
          </li>
          <li>
            <a href="#">О КубГТУ</a>
          </li>
        </ul>
      </div>
      
      <div class="base-footer__section">
        <h3>Социальные сети</h3>
        <ul>
          <li>
            <a href="#" target="_blank" rel="noopener">VK</a>
          </li>
          <li>
            <a href="#" target="_blank" rel="noopener">Telegram</a>
          </li>
        </ul>
      </div>
      
      <slot name="additional" />
    </div>
    
    <div class="base-footer__copyright">
      <p>&copy; {{ currentYear }} КубГТУ. Все права защищены.</p>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped>
@import '@/Styles/layout.css';
</style>






