<?php

namespace Database\Seeders;

use App\Domain\CreditCard\Models\CreditCard;
use App\Domain\CreditCardCharge\Models\CreditCardCharge;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class CreditCardChargeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $c6 = CreditCard::forUser($user->uid)->where('name', 'C6 BANK 1')->first();

            if (! $c6) {
                continue;
            }

            $charges = [
                // À vista — Abr/2026
                ['description' => 'SUPERMERCADOS BH', 'amount' => 116.00, 'total_installments' => 1, 'purchase_date' => '2026-04-16'],
                ['description' => 'CINEART', 'amount' => 39.00, 'total_installments' => 1, 'purchase_date' => '2026-04-12'],
                ['description' => 'MC DONUTS MINAS', 'amount' => 38.00, 'total_installments' => 1, 'purchase_date' => '2026-04-12'],
                ['description' => 'AUTO POSTO BARUC LTDA', 'amount' => 178.09, 'total_installments' => 1, 'purchase_date' => '2026-04-12'],

                // Recorrentes — Abr/2026
                ['description' => 'SPOTIFY', 'amount' => 31.90, 'total_installments' => 1, 'purchase_date' => '2026-04-13'],
                ['description' => 'AMAZON PRIME', 'amount' => 19.90, 'total_installments' => 1, 'purchase_date' => '2026-04-02'],
                ['description' => 'NETFLIX', 'amount' => 59.90, 'total_installments' => 1, 'purchase_date' => '2026-04-02'],

                // Parceladas
                ['description' => 'RIACHUELO', 'amount' => round(179.98 * 3, 2), 'total_installments' => 3, 'purchase_date' => '2026-02-14'],
                ['description' => 'PONTO M MINAS SHOPPING', 'amount' => round(427.34 * 5, 2), 'total_installments' => 5, 'purchase_date' => '2026-01-06'],
                ['description' => 'LEROY MERLIN', 'amount' => round(148.46 * 6, 2), 'total_installments' => 6, 'purchase_date' => '2025-12-23'],
                ['description' => 'SHOPEE ELVORANEWPET', 'amount' => round(30.59 * 7, 2), 'total_installments' => 7, 'purchase_date' => '2026-03-01'],
                ['description' => 'SHOPEE MEURODAPLOJAOF', 'amount' => round(63.36 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2026-02-13'],
                ['description' => 'SHOPEE SHPSTECNOLOGIA', 'amount' => round(53.64 * 9, 2), 'total_installments' => 9, 'purchase_date' => '2026-02-13'],
                ['description' => 'AMAZON BR', 'amount' => round(53.95 * 7, 2), 'total_installments' => 7, 'purchase_date' => '2026-01-11'],
                ['description' => 'AMAZON ENGAGEELE 1', 'amount' => round(183.25 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2025-12-27'],
                ['description' => 'AMAZON ENGAGEELE 2', 'amount' => round(241.58 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2025-12-20'],
                ['description' => 'AMAZON LOJAELECT', 'amount' => round(356.57 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2025-12-20'],
                ['description' => 'AMAZON BR 2', 'amount' => round(109.56 * 10, 2), 'total_installments' => 10, 'purchase_date' => '2025-11-27'],
                ['description' => 'AMAZON BOOKBRASIL', 'amount' => round(53.54 * 9, 2), 'total_installments' => 9, 'purchase_date' => '2025-08-07'],
                ['description' => 'AMAZON MARKETPLACE', 'amount' => round(81.30 * 10, 2), 'total_installments' => 10, 'purchase_date' => '2025-08-03'],
                ['description' => 'PICHAU INFORMÁTICA', 'amount' => round(123.55 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2025-06-06'],
                ['description' => 'AMAZON MMSCOMERC', 'amount' => round(33.97 * 5, 2), 'total_installments' => 5, 'purchase_date' => '2026-02-13'],
                ['description' => 'AMAZON BELEZAVAR', 'amount' => round(29.87 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2026-02-06'],
                ['description' => 'MP CANDIDE', 'amount' => round(33.74 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2025-11-02'],
            ];

            foreach ($charges as $charge) {
                CreditCardCharge::create(array_merge($charge, [
                    'credit_card_uid' => $c6->uid,
                ]));
            }

            // NUBANK 1
            $nubank = CreditCard::forUser($user->uid)->where('name', 'NUBANK 1')->first();

            if ($nubank) {
                $nubankCharges = [
                    ['description' => 'EBN *EPICGAMES', 'amount' => 39.33, 'total_installments' => 1, 'purchase_date' => '2026-04-20'],
                    ['description' => 'MEU PAO', 'amount' => 9.83, 'total_installments' => 1, 'purchase_date' => '2026-04-12'],
                    ['description' => 'PICHAU INFORMÁTICA', 'amount' => round(33.49 * 12, 2), 'total_installments' => 12, 'purchase_date' => '2025-05-11'],
                    ['description' => 'MAGALU MAGAZINE LUIZA', 'amount' => round(359.90 * 10, 2), 'total_installments' => 10, 'purchase_date' => '2026-01-11'],
                    ['description' => 'AMAZON SILVEIRAG', 'amount' => round(46.47 * 3, 2), 'total_installments' => 3, 'purchase_date' => '2026-03-11'],
                ];

                foreach ($nubankCharges as $charge) {
                    CreditCardCharge::create(array_merge($charge, [
                        'credit_card_uid' => $nubank->uid,
                    ]));
                }
            }

            // CASAS BAHIA 1
            $casasBahia = CreditCard::forUser($user->uid)->where('name', 'CASAS BAHIA 1')->first();

            if ($casasBahia) {
                $casasBahiaCharges = [
                    ['description' => 'IPHONE 16', 'amount' => round(231.00 * 30, 2), 'total_installments' => 30, 'purchase_date' => '2026-03-10'],
                    ['description' => 'APARADOR', 'amount' => round(35.00 * 30, 2), 'total_installments' => 30, 'purchase_date' => '2026-03-10'],
                ];

                foreach ($casasBahiaCharges as $charge) {
                    CreditCardCharge::create(array_merge($charge, [
                        'credit_card_uid' => $casasBahia->uid,
                    ]));
                }
            }
        }
    }
}
