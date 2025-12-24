<template>
    <div class="space-y-6">
        <Head title="Профиль партнера" />
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
                <!-- Информация о компании -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Информация о компании</h3>
                            <p class="mt-1 text-sm text-gray-500">Основная информация о вашей компании</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <!-- Фотография профиля -->
                            <AvatarEditor
                                :user="user"
                                avatar-route-prefix="partner"
                                :editable="isEditing"
                                v-model="avatarFile"
                            />

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
                                <label for="inn" class="block text-sm font-medium text-gray-700 mb-1">
                                    ИНН
                                </label>
                                <input
                                    type="text"
                                    id="inn"
                                    v-model="form.inn"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="ИНН компании"
                                />
                                <div v-if="errors.inn" class="mt-1 text-sm text-red-600">{{ errors.inn }}</div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Адрес
                                </label>
                                <input
                                    type="text"
                                    id="address"
                                    v-model="form.address"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Полный адрес компании"
                                />
                                <div v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</div>
                            </div>

                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                                    Веб-сайт
                                </label>
                                <input
                                    type="url"
                                    id="website"
                                    v-model="form.website"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="https://example.com"
                                />
                                <div v-if="errors.website" class="mt-1 text-sm text-red-600">{{ errors.website }}</div>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    Описание
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="Опишите вашу компанию, сферу деятельности и цели..."
                                ></textarea>
                                <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
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
                                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                                    Контактное лицо
                                </label>
                                <input
                                    type="text"
                                    id="contact_person"
                                    v-model="form.contact_person"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="ФИО контактного лица"
                                />
                                <div v-if="errors.contact_person" class="mt-1 text-sm text-red-600">{{ errors.contact_person }}</div>
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Контактный телефон
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
                        </div>
                    </div>
                </div>

                <!-- Основная информация пользователя -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Основная информация</h3>
                            <p class="mt-1 text-sm text-gray-500">Имя и email пользователя</p>
                        </div>
                        <div class="md:col-span-2 space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    ФИО *
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    v-model="form.name"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="ФИО"
                                />
                                <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email *
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    v-model="form.email"
                                    :readonly="!isEditing"
                                    :disabled="!isEditing || processing"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm read-only:bg-gray-50 read-only:text-gray-700 read-only:border-gray-200 disabled:opacity-50"
                                    placeholder="email@example.com"
                                />
                                <div v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AvatarEditor from '@/Components/Shared/AvatarEditor.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    partnerProfile: {
        type: Object,
        required: true
    }
});

// Форма по моделям:
// User: name, email, avatar
// PartnerProfile: company_name, inn, address, website, description, contact_person, contact_phone
const initialForm = {
    // User fields
    name: props.user.name || '',
    email: props.user.email || '',
    
    // PartnerProfile fields
    company_name: props.partnerProfile?.company_name || '',
    inn: props.partnerProfile?.inn || '',
    address: props.partnerProfile?.address || '',
    website: props.partnerProfile?.website || '',
    description: props.partnerProfile?.description || '',
    contact_person: props.partnerProfile?.contact_person || '',
    contact_phone: props.partnerProfile?.contact_phone || ''
};

const form = reactive({ ...initialForm });
const isEditing = ref(false);
const processing = ref(false);
const errors = ref({});
const avatarFile = ref(null);

const startEditing = () => {
    isEditing.value = true;
};

const cancelEdit = () => {
    // Reset form to initial values
    Object.assign(form, initialForm);
    avatarFile.value = null;
    isEditing.value = false;
    errors.value = {};
};

const submitForm = () => {
    processing.value = true;
    errors.value = {};

    const hasAvatar = avatarFile.value !== null && avatarFile.value instanceof File;

    // Подготавливаем данные
    const submitData = {
        name: form.name,
        email: form.email,
        company_name: form.company_name,
        inn: form.inn || '',
        address: form.address || '',
        website: form.website || '',
        description: form.description || '',
        contact_person: form.contact_person || '',
        contact_phone: form.contact_phone || ''
    };

    if (hasAvatar) {
        submitData.avatar = avatarFile.value;
    }

    // Для PUT запросов с файлами нужно использовать POST с _method: PUT
    if (hasAvatar) {
        const formData = new FormData();
        Object.keys(submitData).forEach(key => {
            if (submitData[key] !== null && submitData[key] !== undefined) {
                if (submitData[key] instanceof File) {
                    formData.append(key, submitData[key]);
                } else {
                    formData.append(key, submitData[key]);
                }
            }
        });
        formData.append('_method', 'PUT');

        router.post(route('partner.profile.update'), formData, {
            preserveState: true,
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                isEditing.value = false;
                processing.value = false;
                avatarFile.value = null;
                // Update the initial form data with new values
                Object.assign(initialForm, {
                    name: form.name,
                    email: form.email,
                    company_name: form.company_name,
                    inn: form.inn,
                    address: form.address,
                    website: form.website,
                    description: form.description,
                    contact_person: form.contact_person,
                    contact_phone: form.contact_phone
                });
            },
            onError: (err) => {
                processing.value = false;
                errors.value = err;
            }
        });
    } else {
        // Без файлов можно использовать обычный PUT
        router.put(route('partner.profile.update'), submitData, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
                processing.value = false;
                // Update the initial form data with new values
                Object.assign(initialForm, {
                    name: form.name,
                    email: form.email,
                    company_name: form.company_name,
                    inn: form.inn,
                    address: form.address,
                    website: form.website,
                    description: form.description,
                    contact_person: form.contact_person,
                    contact_phone: form.contact_phone
                });
            },
            onError: (err) => {
                processing.value = false;
                errors.value = err;
            }
        });
    }
};
</script>

