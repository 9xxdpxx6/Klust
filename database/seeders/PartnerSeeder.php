<?php

namespace Database\Seeders;

use App\Models\PartnerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            'Сбер',
            'Тинькофф',
            'Яндекс',
            'Ростех',
            'Лукойл',
            'Газпром',
            'Роснефть',
            'МТС',
            'Мегафон',
            'VK',
            'Ростелеком',
            'Альфа-Банк',
            'X5 Retail Group',
            'Магнит',
            'Wildberries',
        ];

        $partners = User::role('partner')->orderBy('id')->get();

        if ($partners->isEmpty()) {
            return;
        }

        foreach ($partners as $index => $partner) {
            $companyName = $companies[$index % count($companies)];

            $description = $this->getDescription($companyName);
            $website = $this->getWebsite($companyName);

            $profile = $partner->partnerProfile ?? new PartnerProfile();
            $profile->fill([
                'user_id' => $partner->id,
                'company_name' => $companyName,
                'description' => $description,
                'website' => $website,
                'is_active' => true,
            ])->save();
        }
    }

    private function getDescription(string $company): string
    {
        return match ($company) {
            'Сбер' => 'Крупнейший банк России. Участвует в симуляторе «Банковская сеть: Оптимизация филиалов».',
            'Тинькофф' => 'Цифровой банк и экосистема. Фокус — клиентский опыт и автоматизация процессов.',
            'Яндекс' => 'Технологический лидер. Участвует в симуляторе «Логистический центр: Распределение ресурсов».',
            'Ростех' => 'Госкорпорация высоких технологий. Участвует в симуляторе «Производство: Оптимизация линий».',
            'Лукойл' => 'Нефтегазовый холдинг с глобальным присутствием. Интересуется управлением цепочками поставок.',
            'Газпром' => 'Энергетический гигант. Задачи — оптимизация инфраструктуры и цифровизация активов.',
            'Роснефть' => 'Одна из крупнейших нефтедобывающих компаний мира. Фокус — эффективность производства.',
            'МТС' => 'Телеком-лидер и цифровой провайдер. Участвует в симуляторе «Розничная сеть: Управление запасами».',
            'Мегафон' => 'Оператор связи и цифровых решений. Интересуется аналитикой и управлением ресурсами.',
            'VK' => 'Цифровая экосистема. Участвует в симуляторе «IT-инфраструктура: Масштабирование сервисов».',
            'Ростелеком' => 'Национальный оператор связи. Задачи — цифровая трансформация и управление проектами.',
            'Альфа-Банк' => 'Крупнейший частный банк. Интересуется риск-менеджментом и клиентскими решениями.',
            'X5 Retail Group' => 'Крупнейшая розничная сеть (Пятёрочка, Перекрёсток). Задачи — логистика и управление ассортиментом.',
            'Магнит' => 'Лидер discount-ритейла. Фокус — оптимизация запасов и эффективность магазинов.',
            'Wildberries' => 'Крупнейший маркетплейс. Интересуется логистикой последней мили и автоматизацией складов.',
            default => "Партнёр проекта: {$company}. Поддерживает образовательные симуляции и кейсы для студентов.",
        };
    }

    private function getWebsite(string $company): ?string
    {
        return match ($company) {
            'Сбер' => 'https://www.sberbank.ru',
            'Тинькофф' => 'https://www.tinkoff.ru',
            'Яндекс' => 'https://yandex.ru',
            'Ростех' => 'https://rostec.ru',
            'Лукойл' => 'https://www.lukoil.ru',
            'Газпром' => 'https://www.gazprom.ru',
            'Роснефть' => 'https://www.rosneft.ru',
            'МТС' => 'https://moscow.mts.ru',
            'Мегафон' => 'https://moscow.megafon.ru',
            'VK' => 'https://vk.company',
            'Ростелеком' => 'https://rt.ru',
            'Альфа-Банк' => 'https://alfabank.ru',
            'X5 Retail Group' => 'https://www.x5.ru',
            'Магнит' => 'https://www.magnit.ru',
            'Wildberries' => 'https://seller.wildberries.ru', // для партнёров/продавцов — логично
            default => 'https://example.com', // fallback
        };
    }
}
