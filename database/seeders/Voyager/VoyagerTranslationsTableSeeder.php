<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerTranslationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('translations')->delete();

        \DB::table('translations')->insert(array (
            0 =>
            array (
                'id' => 1,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 10,
                'locale' => 'en',
                'value' => 'Settings',
                'created_at' => '2021-06-06 18:22:09',
                'updated_at' => '2021-06-06 18:22:09',
            ),
            1 =>
            array (
                'id' => 2,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 24,
                'locale' => 'en',
                'value' => 'Propositions',
                'created_at' => '2021-06-06 18:23:32',
                'updated_at' => '2021-06-06 18:23:32',
            ),
            2 =>
            array (
                'id' => 3,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 13,
                'locale' => 'en',
                'value' => 'Tickets',
                'created_at' => '2021-06-06 18:23:42',
                'updated_at' => '2021-06-06 18:23:42',
            ),
            3 =>
            array (
                'id' => 4,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 26,
                'locale' => 'en',
                'value' => 'Spaces',
                'created_at' => '2021-06-06 18:24:01',
                'updated_at' => '2021-06-06 18:24:01',
            ),
            4 =>
            array (
                'id' => 5,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 14,
                'locale' => 'en',
                'value' => 'Services',
                'created_at' => '2021-06-06 18:24:09',
                'updated_at' => '2021-06-06 18:24:09',
            ),
            5 =>
            array (
                'id' => 6,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 21,
                'locale' => 'en',
                'value' => 'Imperia Menus',
                'created_at' => '2021-06-06 18:24:38',
                'updated_at' => '2021-06-06 18:24:38',
            ),
            6 =>
            array (
                'id' => 7,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 22,
                'locale' => 'en',
                'value' => 'Products',
                'created_at' => '2021-06-06 18:24:46',
                'updated_at' => '2021-06-06 18:24:46',
            ),
            7 =>
            array (
                'id' => 8,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 28,
                'locale' => 'en',
                'value' => 'Discounts',
                'created_at' => '2021-06-06 18:25:09',
                'updated_at' => '2021-06-06 18:25:09',
            ),
            8 =>
            array (
                'id' => 9,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 23,
                'locale' => 'en',
                'value' => 'Categories',
                'created_at' => '2021-06-06 18:25:22',
                'updated_at' => '2021-06-06 18:25:22',
            ),
            9 =>
            array (
                'id' => 10,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 12,
                'locale' => 'en',
                'value' => 'Ticket Categories',
                'created_at' => '2021-06-06 18:25:40',
                'updated_at' => '2021-06-06 18:25:40',
            ),
            10 =>
            array (
                'id' => 11,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 25,
                'locale' => 'en',
                'value' => 'Space Categories',
                'created_at' => '2021-06-06 18:26:17',
                'updated_at' => '2021-06-06 18:26:17',
            ),
            11 =>
            array (
                'id' => 12,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 16,
                'locale' => 'en',
                'value' => 'Service Categories',
                'created_at' => '2021-06-06 18:26:27',
                'updated_at' => '2021-06-06 18:26:27',
            ),
            12 =>
            array (
                'id' => 13,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 19,
                'locale' => 'en',
                'value' => 'Product Categories',
                'created_at' => '2021-06-06 18:26:39',
                'updated_at' => '2021-06-06 18:26:39',
            ),
            13 =>
            array (
                'id' => 14,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 20,
                'locale' => 'en',
                'value' => 'Menu Categories',
                'created_at' => '2021-06-06 18:27:03',
                'updated_at' => '2021-06-06 18:27:03',
            ),
            14 =>
            array (
                'id' => 15,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 27,
                'locale' => 'en',
                'value' => 'Discount Categories',
                'created_at' => '2021-06-06 18:27:15',
                'updated_at' => '2021-06-06 18:27:15',
            ),
            15 =>
            array (
                'id' => 16,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 31,
                'locale' => 'en',
                'value' => 'Access',
                'created_at' => '2021-06-06 18:27:26',
                'updated_at' => '2021-06-06 18:27:26',
            ),
            16 =>
            array (
                'id' => 17,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 30,
                'locale' => 'en',
                'value' => 'Imperia Users',
                'created_at' => '2021-06-06 18:27:41',
                'updated_at' => '2021-06-06 18:27:41',
            ),
            17 =>
            array (
                'id' => 18,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 29,
                'locale' => 'en',
                'value' => 'Imperia Roles',
                'created_at' => '2021-06-06 18:28:06',
                'updated_at' => '2021-06-06 18:28:06',
            ),
            18 =>
            array (
                'id' => 19,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 4,
                'locale' => 'en',
                'value' => 'Roles',
                'created_at' => '2021-06-06 18:28:30',
                'updated_at' => '2021-06-06 18:28:30',
            ),
            19 =>
            array (
                'id' => 20,
                'table_name' => 'menu_items',
                'column_name' => 'title',
                'foreign_key' => 1,
                'locale' => 'en',
                'value' => 'Dashboard',
                'created_at' => '2021-06-06 18:29:17',
                'updated_at' => '2021-06-06 18:29:17',
            ),
        ));


    }
}
