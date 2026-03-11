<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        $products = [];

        for ($i = 0; $i < 30; $i++) {
            $cost = $faker->randomFloat(2, 5, 500);
            $isVat = $faker->boolean(70);

            $products[] = [
                'name' => $faker->words(3, true),
                'code' => strtoupper($faker->bothify('??-####')),
                'cost_price' => $cost,
                'sale_price' => $cost * $faker->randomFloat(2, 1.2, 1.8),
                'is_vat_applicable' => $isVat ? 1 : 0,
                'vat_percent' => $isVat ? 15.00 : 0.00,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->table('products')->insertBatch($products);
    }
}