<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900">Профиль партнера</h1>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex justify-end mb-6">
                <button
                    v-if="!isEditing"
                    @click="startEditing"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out"
                >
                    Редактировать
                </button>
                <div v-else class="flex space-x-3">
                    <button
                        @click="cancelEdit"
                        class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Отмена
                    </button>
                    <button
                        @click="submitForm"
                        :disabled="processing"
                        class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none disabled:opacity-50"
                    >
                        <span v-if="processing">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Сохранение...
                        </span>
                        <span v-else>Сохранить</span>
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Логотип и информация о компании -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <h3 class="text-lg font-medium text-gray-900">Логотип компании</h3>
                        <p class="mt-1 text-sm text-gray-500">Загрузите логотип вашей компании</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-16 rounded-md overflow-hidden">
                                <img 
                                    v-if="form.logo_url || previewLogo" 
                                    :src="previewLogo || form.logo_url" 
                                    :alt="form.company_name || 'Логотип'"
                                    class="h-full w-full object-cover rounded-md"
                                />
                                <div v-else class="h-full w-full bg-gray-200 rounded-md flex items-center justify-center">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <label class="block">
                                    <span class="sr-only">Выберите логотип</span>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="onLogoChange"
                                        :disabled="!isEditing || processing"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 disabled:opacity-50"
                                    />
                                </label>
                                <button
                                    v-if="form.logo_url || previewLogo"
                                    @click="clearLogo"
                                    :disabled="!isEditing || processing"
                                    type="button"
                                    class="mt-2 text-sm text-red-600 hover:text-red-900 disabled:opacity-50"
                                >
                                    Удалить логотип
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Информация о компании -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Информация о компании</h3>
                            <p class="mt-1 text-sm text-gray-500">Основная информация о вашей компании</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Название компании *
                                </label>
                                <input
                                    type="text"
                                    id="company_name"
                                    v-model="form.company_name"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Например: ООО 'ТехноИнновации'"
                                />
                                <div v-if="errors.company_name" class="mt-1 text-sm text-red-600">{{ errors.company_name }}</div>
                            </div>

                            <div>
                                <label for="company_description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Описание
                                </label>
                                <textarea
                                    id="company_description"
                                    v-model="form.company_description"
                                    rows="3"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Опишите вашу компанию, сферу деятельности и цели..."
                                ></textarea>
                                <div v-if="errors.company_description" class="mt-1 text-sm text-red-600">{{ errors.company_description }}</div>
                            </div>

                            <div>
                                <label for="company_website" class="block text-sm font-medium text-gray-700 mb-1">
                                    Веб-сайт
                                </label>
                                <input
                                    type="url"
                                    id="company_website"
                                    v-model="form.company_website"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="https://example.com"
                                />
                                <div v-if="errors.company_website" class="mt-1 text-sm text-red-600">{{ errors.company_website }}</div>
                            </div>

                            <div>
                                <label for="company_address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Адрес
                                </label>
                                <input
                                    type="text"
                                    id="company_address"
                                    v-model="form.company_address"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Полный адрес компании"
                                />
                                <div v-if="errors.company_address" class="mt-1 text-sm text-red-600">{{ errors.company_address }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Контактная информация -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Контактная информация</h3>
                            <p class="mt-1 text-sm text-gray-500">Контактное лицо и способы связи</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label for="contact_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Контактное лицо *
                                </label>
                                <input
                                    type="text"
                                    id="contact_name"
                                    v-model="form.contact_name"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="ФИО контактного лица"
                                />
                                <div v-if="errors.contact_name" class="mt-1 text-sm text-red-600">{{ errors.contact_name }}</div>
                            </div>

                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email *
                                </label>
                                <input
                                    type="email"
                                    id="contact_email"
                                    v-model="form.contact_email"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="email@example.com"
                                />
                                <div v-if="errors.contact_email" class="mt-1 text-sm text-red-600">{{ errors.contact_email }}</div>
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Телефон
                                </label>
                                <input
                                    type="tel"
                                    id="contact_phone"
                                    v-model="form.contact_phone"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="+7 (XXX) XXX-XX-XX"
                                />
                                <div v-if="errors.contact_phone" class="mt-1 text-sm text-red-600">{{ errors.contact_phone }}</div>
                            </div>

                            <div>
                                <label for="contact_telegram" class="block text-sm font-medium text-gray-700 mb-1">
                                    Telegram
                                </label>
                                <input
                                    type="text"
                                    id="contact_telegram"
                                    v-model="form.contact_telegram"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="@username или +7XXXXXXXXXX"
                                />
                                <div v-if="errors.contact_telegram" class="mt-1 text-sm text-red-600">{{ errors.contact_telegram }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    partnerProfile: {
        type: Object,
        required: true
    },
    partner: {
        type: Object,
        required: true
    }
});

// Combine form data from partner and user
const initialForm = {
    logo_url: props.partner.logo_url,
    company_name: props.partner.company_name,
    company_description: props.partner.description,
    company_website: props.partner.website,
    company_address: props.partner.address,
    contact_name: props.user.name,
    contact_email: props.user.email,
    contact_phone: props.user.phone || '',
    contact_telegram: props.user.telegram || ''
};

const form = reactive({ ...initialForm });
const isEditing = ref(false);
const processing = ref(false);
const errors = ref({});
const previewLogo = ref(null);

const startEditing = () => {
    isEditing.value = true;
};

const cancelEdit = () => {
    // Reset form to initial values
    Object.assign(form, initialForm);
    previewLogo.value = null;
    isEditing.value = false;
    errors.value = {};
};

const onLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        previewLogo.value = URL.createObjectURL(file);
        form.logo_file = file;
    }
};

const clearLogo = () => {
    previewLogo.value = null;
    form.logo_file = null;
    // If we have an existing logo, set it back to the original
    form.logo_url = initialForm.logo_url;
};

const submitForm = () => {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('company_name', form.company_name);
    formData.append('description', form.company_description);
    formData.append('website', form.company_website);
    formData.append('address', form.company_address);
    formData.append('contact_name', form.contact_name);
    formData.append('contact_email', form.contact_email);
    formData.append('phone', form.contact_phone);
    formData.append('telegram', form.contact_telegram);
    
    if (form.logo_file) {
        formData.append('logo', form.logo_file);
    }

    router.put(route('partner.profile.update'), formData, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            processing.value = false;
            // Update the initial form data with new values
            Object.assign(initialForm, {
                logo_url: form.logo_url,
                company_name: form.company_name,
                company_description: form.company_description,
                company_website: form.company_website,
                company_address: form.company_address,
                contact_name: form.contact_name,
                contact_email: form.contact_email,
                contact_phone: form.contact_phone,
                contact_telegram: form.contact_telegram
            });
        },
        onError: (err) => {
            processing.value = false;
            errors.value = err;
        }
    });
};
</script>