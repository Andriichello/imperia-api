<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('products')->delete();

        \DB::table('products')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Mojito',
                'description' => 'Iced Sprite with mint, lime and lemon.',
                'price' => '120.00',
                'weight' => '250.00',
                'menu_id' => 1,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Simpleton Cocktail',
                'description' => 'A sparkling combination of juice and vodka.',
                'price' => '100.00',
                'weight' => '200.00',
                'menu_id' => 1,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Martini',
                'description' => NULL,
                'price' => '120.00',
                'weight' => '150.00',
                'menu_id' => 1,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Pear Mimosa',
                'description' => 'Champagne and pear nectar combine in a delicate drink.',
                'price' => '180.00',
                'weight' => '150.00',
                'menu_id' => 1,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'Iced Tea With Plums and Thyme',
                'description' => 'Served nonalcoholic fruit-and-herb blend sipper.',
                'price' => '120.00',
                'weight' => '250.00',
                'menu_id' => 2,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'Iced Green Tea With Ginger and Mint',
                'description' => 'Green tea is spiced with ginger and mint.',
                'price' => '100.00',
                'weight' => '200.00',
                'menu_id' => 2,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'Fresh-Strawberry-Shake',
                'description' => 'Fresh strawberries are at the heart of these thick and totally decadent frappes.',
                'price' => '100.00',
                'weight' => '200.00',
                'menu_id' => 2,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'Creamy Caramel Latt├й',
                'description' => 'Late with a touch of caramel.',
                'price' => '85.00',
                'weight' => '180.00',
                'menu_id' => 1,
                'category_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'Spiced Hot Cocoa',
                'description' => 'Hot cocoa with a whipped cream and cinnamon',
                'price' => '63.00',
                'weight' => '250.00',
                'menu_id' => 1,
                'category_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            9 =>
            array (
                'id' => 10,
                'name' => 'Warm Hazelnut Toddy',
                'description' => NULL,
                'price' => '112.00',
                'weight' => '250.00',
                'menu_id' => 1,
                'category_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            10 =>
            array (
                'id' => 11,
                'name' => 'Margarita',
                'description' => 'The simplest pizza ever. Cheese, basil and few slices of tomato.',
                'price' => '90.00',
                'weight' => '400.00',
                'menu_id' => 3,
                'category_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            11 =>
            array (
                'id' => 12,
                'name' => 'Romana',
                'description' => 'Cheese, beacon, arugula and parmesan.',
                'price' => '130.00',
                'weight' => '450.00',
                'menu_id' => 3,
                'category_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            12 =>
            array (
                'id' => 13,
                'name' => 'Ortolana',
                'description' => 'Mushrooms, zucchini, eggplant, parmesan and sliced parsley.',
                'price' => '130.00',
                'weight' => '400.00',
                'menu_id' => 3,
                'category_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            13 =>
            array (
                'id' => 14,
                'name' => 'Quatro stagioni',
                'description' => 'As the italian name quotes this is a pizza of four seasons.',
                'price' => '150.00',
                'weight' => '450.00',
                'menu_id' => 3,
                'category_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            14 =>
            array (
                'id' => 15,
                'name' => 'Quatro formagi',
                'description' => 'As the italian name quotes this is a pizza of four cheeses.',
                'price' => '180.00',
                'weight' => '400.00',
                'menu_id' => 3,
                'category_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            15 =>
            array (
                'id' => 16,
                'name' => 'Tomato soup',
                'description' => NULL,
                'price' => '83.00',
                'weight' => '400.00',
                'menu_id' => 3,
                'category_id' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            16 =>
            array (
                'id' => 17,
                'name' => 'Parsnip soup with toasted hazelnuts',
                'description' => NULL,
                'price' => '120.00',
                'weight' => '300.00',
                'menu_id' => 3,
                'category_id' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            17 =>
            array (
                'id' => 18,
                'name' => 'Celery soup',
                'description' => NULL,
                'price' => '140.00',
                'weight' => '250.00',
                'menu_id' => 3,
                'category_id' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
