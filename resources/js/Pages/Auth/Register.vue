<template>
    <GuestLayout title="Регистрация">
        <div class="space-y-6">
            <!-- Выбор типа регистрации -->
            <div class="flex space-x-4 border-b border-gray-200 pb-4">
                <button
                    @click="registrationType = 'student'"
                    :class="[
                        'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors',
                        registrationType === 'student'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]"
                >
                    Студент
                </button>
                <button
                    @click="registrationType = 'partner'"
                    :class="[
                        'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors',
                        registrationType === 'partner'
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]"
                >
                    Партнер
                </button>
            </div>

            <!-- Форма студента -->
            <form v-if="registrationType === 'student'" @submit.prevent="submitStudent" class="space-y-6">
                <!-- KubGTU ID -->
                <div>
                    <label for="kubgtu_id" class="block text-sm font-medium text-gray-700">
                        ID КубГТУ <span class="text-gray-400">(необязательно)</span>
                    </label>
                    <div class="mt-1">
                        <input
                            id="kubgtu_id"
                            v-model="studentForm.kubgtu_id"
                            type="text"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': studentForm.errors.kubgtu_id }"
                            placeholder="Введите ID КубГТУ"
                        />
                        <p v-if="studentForm.errors.kubgtu_id" class="mt-2 text-sm text-red-600">
                            {{ studentForm.errors.kubgtu_id }}
                        </p>
                    </div>
                </div>

                <!-- ФИО -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        ФИО *
                    </label>
                    <div class="mt-1">
                        <input
                            id="name"
                            v-model="studentForm.name"
                            type="text"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': studentForm.errors.name }"
                            placeholder="Введите ФИО"
                        />
                        <p v-if="studentForm.errors.name" class="mt-2 text-sm text-red-600">
                            {{ studentForm.errors.name }}
                        </p>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="student_email" class="block text-sm font-medium text-gray-700">
                        Email *
                    </label>
                    <div class="mt-1">
                        <input
                            id="student_email"
                            v-model="studentForm.email"
                            type="email"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': studentForm.errors.email }"
                            placeholder="Введите email"
                        />
                        <p v-if="studentForm.errors.email" class="mt-2 text-sm text-red-600">
                            {{ studentForm.errors.email }}
                        </p>
                    </div>
                </div>

                <!-- Курс -->
                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700">
                        Курс *
                    </label>
                    <div class="mt-1">
                        <select
                            id="course"
                            v-model="studentForm.course"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': studentForm.errors.course }"
                        >
                            <option value="">Выберите курс</option>
                            <option v-for="i in 6" :key="i" :value="i">{{ i }}</option>
                        </select>
                        <p v-if="studentForm.errors.course" class="mt-2 text-sm text-red-600">
                            {{ studentForm.errors.course }}
                        </p>
                    </div>
                </div>

                <!-- Пароль -->
                <div>
                    <label for="student_password" class="block text-sm font-medium text-gray-700">
                        Пароль *
                    </label>
                    <div class="mt-1">
                        <input
                            id="student_password"
                            v-model="studentForm.password"
                            type="password"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': studentForm.errors.password }"
                            placeholder="Минимум 8 символов"
                        />
                        <p v-if="studentForm.errors.password" class="mt-2 text-sm text-red-600">
                            {{ studentForm.errors.password }}
                        </p>
                    </div>
                </div>

                <!-- Подтверждение пароля -->
                <div>
                    <label for="password_confirmation_student" class="block text-sm font-medium text-gray-700">
                        Подтверждение пароля *
                    </label>
                    <div class="mt-1">
                        <input
                            id="password_confirmation_student"
                            v-model="studentForm.password_confirmation"
                            type="password"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="studentForm.processing"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="studentForm.processing">Регистрация...</span>
                    <span v-else>Зарегистрироваться</span>
                </button>
            </form>

            <!-- Форма партнера -->
            <form v-else @submit.prevent="submitPartner" class="space-y-6">
                <!-- Название компании -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700">
                        Название компании *
                    </label>
                    <div class="mt-1">
                        <input
                            id="company_name"
                            v-model="partnerForm.company_name"
                            type="text"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': partnerForm.errors.company_name }"
                            placeholder="Введите название компании"
                        />
                        <p v-if="partnerForm.errors.company_name" class="mt-2 text-sm text-red-600">
                            {{ partnerForm.errors.company_name }}
                        </p>
                    </div>
                </div>

                <!-- Контактное лицо -->
                <div>
                    <label for="contact_person" class="block text-sm font-medium text-gray-700">
                        Контактное лицо (ФИО) *
                    </label>
                    <div class="mt-1">
                        <input
                            id="contact_person"
                            v-model="partnerForm.contact_person"
                            type="text"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': partnerForm.errors.contact_person }"
                            placeholder="Введите ФИО контактного лица"
                        />
                        <p v-if="partnerForm.errors.contact_person" class="mt-2 text-sm text-red-600">
                            {{ partnerForm.errors.contact_person }}
                        </p>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="partner_email" class="block text-sm font-medium text-gray-700">
                        Email *
                    </label>
                    <div class="mt-1">
                        <input
                            id="partner_email"
                            v-model="partnerForm.email"
                            type="email"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': partnerForm.errors.email }"
                            placeholder="Введите email"
                        />
                        <p v-if="partnerForm.errors.email" class="mt-2 text-sm text-red-600">
                            {{ partnerForm.errors.email }}
                        </p>
                    </div>
                </div>

                <!-- Телефон -->
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">
                        Телефон *
                    </label>
                    <div class="mt-1">
                        <input
                            id="contact_phone"
                            v-model="partnerForm.contact_phone"
                            type="tel"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': partnerForm.errors.contact_phone }"
                            placeholder="Введите телефон"
                        />
                        <p v-if="partnerForm.errors.contact_phone" class="mt-2 text-sm text-red-600">
                            {{ partnerForm.errors.contact_phone }}
                        </p>
                    </div>
                </div>

                <!-- Описание -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Описание компании
                    </label>
                    <div class="mt-1">
                        <textarea
                            id="description"
                            v-model="partnerForm.description"
                            rows="3"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': partnerForm.errors.description }"
                            placeholder="Краткое описание компании (необязательно)"
                        />
                        <p v-if="partnerForm.errors.description" class="mt-2 text-sm text-red-600">
                            {{ partnerForm.errors.description }}
                        </p>
                    </div>
                </div>

                <!-- Пароль -->
                <div>
                    <label for="partner_password" class="block text-sm font-medium text-gray-700">
                        Пароль *
                    </label>
                    <div class="mt-1">
                        <input
                            id="partner_password"
                            v-model="partnerForm.password"
                            type="password"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            :class="{ 'border-red-500': partnerForm.errors.password }"
                            placeholder="Минимум 8 символов"
                        />
                        <p v-if="partnerForm.errors.password" class="mt-2 text-sm text-red-600">
                            {{ partnerForm.errors.password }}
                        </p>
                    </div>
                </div>

                <!-- Подтверждение пароля -->
                <div>
                    <label for="password_confirmation_partner" class="block text-sm font-medium text-gray-700">
                        Подтверждение пароля *
                    </label>
                    <div class="mt-1">
                        <input
                            id="password_confirmation_partner"
                            v-model="partnerForm.password_confirmation"
                            type="password"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="partnerForm.processing"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="partnerForm.processing">Регистрация...</span>
                    <span v-else>Зарегистрироваться</span>
                </button>
            </form>

            <!-- Ссылка на вход -->
            <div class="text-center pt-4 border-t border-gray-200">
                <Link :href="route('login')" class="text-sm text-indigo-600 hover:text-indigo-500">
                    Уже есть аккаунт? Войти
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const registrationType = ref('student');

const studentForm = useForm({
    kubgtu_id: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    course: '',
});

const partnerForm = useForm({
    company_name: '',
    contact_person: '',
    email: '',
    password: '',
    password_confirmation: '',
    contact_phone: '',
    description: '',
});

const submitStudent = () => {
    studentForm.post(route('register.student'), {
        onFinish: () => {
            studentForm.reset('password', 'password_confirmation');
        },
    });
};

const submitPartner = () => {
    partnerForm.post(route('register.partner'), {
        onFinish: () => {
            partnerForm.reset('password', 'password_confirmation');
        },
    });
};
</script>

