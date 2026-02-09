<template>
  <div class="bank-interface">
    <Tabs v-model:value="activeTab" @update:value="onTabChange">
      <TabList>
        <Tab value="0">Профиль клиента</Tab>
        <Tab value="1">Скоринг</Tab>
        <Tab value="2">История диалога</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <ClientProfileForm 
            :client="client" 
            :editable="false"
          />
        </TabPanel>
        
        <TabPanel value="1">
          <ScoringResults 
            :calculations="calculations"
          />
        </TabPanel>
        
        <TabPanel value="2">
          <DialogueHistory 
            :messages="dialogueMessages"
          />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import Tabs from 'primevue/tabs'
import TabList from 'primevue/tablist'
import Tab from 'primevue/tab'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import ClientProfileForm from './ClientProfileForm.vue'
import ScoringResults from './ScoringResults.vue'
import DialogueHistory from './DialogueHistory.vue'

const props = defineProps({
  client: {
    type: Object,
    default: () => ({})
  },
  calculations: {
    type: Object,
    default: () => ({})
  },
  dialogueMessages: {
    type: Array,
    default: () => []
  },
  activeTab: {
    type: String,
    default: '0'
  }
})

const emit = defineEmits(['update:activeTab'])

const activeTab = ref(props.activeTab)

// Синхронизируем с внешним prop
watch(() => props.activeTab, (newValue) => {
  if (newValue !== activeTab.value) {
    activeTab.value = newValue
  }
})

const onTabChange = (value) => {
  activeTab.value = value
  emit('update:activeTab', value)
}
</script>

<style scoped>
.bank-interface {
  width: 100%;
  height: 100%;
  background: white;
  border-radius: 0.5rem;
  overflow: hidden;
}

:deep(.p-tabs) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

:deep(.p-tablist) {
  background: #1e40af;
  border-radius: 0.5rem 0.5rem 0 0;
}

:deep(.p-tab) {
  color: white;
  padding: 0.75rem 1rem;
}

:deep(.p-tab:hover) {
  background: rgba(255, 255, 255, 0.1);
}

:deep(.p-tab[aria-selected="true"]) {
  background: rgba(255, 255, 255, 0.2);
}

:deep(.p-tabpanels) {
  flex: 1;
  overflow: auto;
  background: white;
}
</style>
