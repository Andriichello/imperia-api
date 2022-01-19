<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('permissions')->delete();

        \DB::table('permissions')->insert(array (
            0 =>
            array (
                'id' => 1,
                'key' => 'browse_admin',
                'table_name' => NULL,
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            1 =>
            array (
                'id' => 2,
                'key' => 'browse_bread',
                'table_name' => NULL,
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            2 =>
            array (
                'id' => 3,
                'key' => 'browse_database',
                'table_name' => NULL,
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            3 =>
            array (
                'id' => 4,
                'key' => 'browse_media',
                'table_name' => NULL,
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            4 =>
            array (
                'id' => 5,
                'key' => 'browse_compass',
                'table_name' => NULL,
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            5 =>
            array (
                'id' => 6,
                'key' => 'browse_menus',
                'table_name' => 'menus',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            6 =>
            array (
                'id' => 7,
                'key' => 'read_menus',
                'table_name' => 'menus',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            7 =>
            array (
                'id' => 8,
                'key' => 'edit_menus',
                'table_name' => 'menus',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            8 =>
            array (
                'id' => 9,
                'key' => 'add_menus',
                'table_name' => 'menus',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            9 =>
            array (
                'id' => 10,
                'key' => 'delete_menus',
                'table_name' => 'menus',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            10 =>
            array (
                'id' => 11,
                'key' => 'browse_roles',
                'table_name' => 'roles',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            11 =>
            array (
                'id' => 12,
                'key' => 'read_roles',
                'table_name' => 'roles',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            12 =>
            array (
                'id' => 13,
                'key' => 'edit_roles',
                'table_name' => 'roles',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            13 =>
            array (
                'id' => 14,
                'key' => 'add_roles',
                'table_name' => 'roles',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            14 =>
            array (
                'id' => 15,
                'key' => 'delete_roles',
                'table_name' => 'roles',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            15 =>
            array (
                'id' => 16,
                'key' => 'browse_users',
                'table_name' => 'users',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            16 =>
            array (
                'id' => 17,
                'key' => 'read_users',
                'table_name' => 'users',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            17 =>
            array (
                'id' => 18,
                'key' => 'edit_users',
                'table_name' => 'users',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            18 =>
            array (
                'id' => 19,
                'key' => 'add_users',
                'table_name' => 'users',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            19 =>
            array (
                'id' => 20,
                'key' => 'delete_users',
                'table_name' => 'users',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            20 =>
            array (
                'id' => 21,
                'key' => 'browse_settings',
                'table_name' => 'settings',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            21 =>
            array (
                'id' => 22,
                'key' => 'read_settings',
                'table_name' => 'settings',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            22 =>
            array (
                'id' => 23,
                'key' => 'edit_settings',
                'table_name' => 'settings',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            23 =>
            array (
                'id' => 24,
                'key' => 'add_settings',
                'table_name' => 'settings',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            24 =>
            array (
                'id' => 25,
                'key' => 'delete_settings',
                'table_name' => 'settings',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
            25 =>
            array (
                'id' => 26,
                'key' => 'browse_hooks',
                'table_name' => NULL,
                'created_at' => '2021-05-27 11:51:33',
                'updated_at' => '2021-05-27 11:51:33',
            ),
            26 =>
            array (
                'id' => 27,
                'key' => 'browse_ticket_categories',
                'table_name' => 'ticket_categories',
                'created_at' => '2021-05-27 12:47:59',
                'updated_at' => '2021-05-27 12:47:59',
            ),
            27 =>
            array (
                'id' => 28,
                'key' => 'read_ticket_categories',
                'table_name' => 'ticket_categories',
                'created_at' => '2021-05-27 12:47:59',
                'updated_at' => '2021-05-27 12:47:59',
            ),
            28 =>
            array (
                'id' => 29,
                'key' => 'edit_ticket_categories',
                'table_name' => 'ticket_categories',
                'created_at' => '2021-05-27 12:47:59',
                'updated_at' => '2021-05-27 12:47:59',
            ),
            29 =>
            array (
                'id' => 30,
                'key' => 'add_ticket_categories',
                'table_name' => 'ticket_categories',
                'created_at' => '2021-05-27 12:47:59',
                'updated_at' => '2021-05-27 12:47:59',
            ),
            30 =>
            array (
                'id' => 31,
                'key' => 'delete_ticket_categories',
                'table_name' => 'ticket_categories',
                'created_at' => '2021-05-27 12:47:59',
                'updated_at' => '2021-05-27 12:47:59',
            ),
            31 =>
            array (
                'id' => 32,
                'key' => 'browse_tickets',
                'table_name' => 'tickets',
                'created_at' => '2021-05-27 12:53:05',
                'updated_at' => '2021-05-27 12:53:05',
            ),
            32 =>
            array (
                'id' => 33,
                'key' => 'read_tickets',
                'table_name' => 'tickets',
                'created_at' => '2021-05-27 12:53:05',
                'updated_at' => '2021-05-27 12:53:05',
            ),
            33 =>
            array (
                'id' => 34,
                'key' => 'edit_tickets',
                'table_name' => 'tickets',
                'created_at' => '2021-05-27 12:53:05',
                'updated_at' => '2021-05-27 12:53:05',
            ),
            34 =>
            array (
                'id' => 35,
                'key' => 'add_tickets',
                'table_name' => 'tickets',
                'created_at' => '2021-05-27 12:53:05',
                'updated_at' => '2021-05-27 12:53:05',
            ),
            35 =>
            array (
                'id' => 36,
                'key' => 'delete_tickets',
                'table_name' => 'tickets',
                'created_at' => '2021-05-27 12:53:05',
                'updated_at' => '2021-05-27 12:53:05',
            ),
            36 =>
            array (
                'id' => 37,
                'key' => 'browse_services',
                'table_name' => 'services',
                'created_at' => '2021-05-30 06:04:50',
                'updated_at' => '2021-05-30 06:04:50',
            ),
            37 =>
            array (
                'id' => 38,
                'key' => 'read_services',
                'table_name' => 'services',
                'created_at' => '2021-05-30 06:04:50',
                'updated_at' => '2021-05-30 06:04:50',
            ),
            38 =>
            array (
                'id' => 39,
                'key' => 'edit_services',
                'table_name' => 'services',
                'created_at' => '2021-05-30 06:04:50',
                'updated_at' => '2021-05-30 06:04:50',
            ),
            39 =>
            array (
                'id' => 40,
                'key' => 'add_services',
                'table_name' => 'services',
                'created_at' => '2021-05-30 06:04:50',
                'updated_at' => '2021-05-30 06:04:50',
            ),
            40 =>
            array (
                'id' => 41,
                'key' => 'delete_services',
                'table_name' => 'services',
                'created_at' => '2021-05-30 06:04:50',
                'updated_at' => '2021-05-30 06:04:50',
            ),
            41 =>
            array (
                'id' => 47,
                'key' => 'browse_service_categories',
                'table_name' => 'service_categories',
                'created_at' => '2021-05-30 06:09:37',
                'updated_at' => '2021-05-30 06:09:37',
            ),
            42 =>
            array (
                'id' => 48,
                'key' => 'read_service_categories',
                'table_name' => 'service_categories',
                'created_at' => '2021-05-30 06:09:37',
                'updated_at' => '2021-05-30 06:09:37',
            ),
            43 =>
            array (
                'id' => 49,
                'key' => 'edit_service_categories',
                'table_name' => 'service_categories',
                'created_at' => '2021-05-30 06:09:37',
                'updated_at' => '2021-05-30 06:09:37',
            ),
            44 =>
            array (
                'id' => 50,
                'key' => 'add_service_categories',
                'table_name' => 'service_categories',
                'created_at' => '2021-05-30 06:09:37',
                'updated_at' => '2021-05-30 06:09:37',
            ),
            45 =>
            array (
                'id' => 51,
                'key' => 'delete_service_categories',
                'table_name' => 'service_categories',
                'created_at' => '2021-05-30 06:09:37',
                'updated_at' => '2021-05-30 06:09:37',
            ),
            46 =>
            array (
                'id' => 62,
                'key' => 'browse_product_categories',
                'table_name' => 'product_categories',
                'created_at' => '2021-05-30 06:19:59',
                'updated_at' => '2021-05-30 06:19:59',
            ),
            47 =>
            array (
                'id' => 63,
                'key' => 'read_product_categories',
                'table_name' => 'product_categories',
                'created_at' => '2021-05-30 06:19:59',
                'updated_at' => '2021-05-30 06:19:59',
            ),
            48 =>
            array (
                'id' => 64,
                'key' => 'edit_product_categories',
                'table_name' => 'product_categories',
                'created_at' => '2021-05-30 06:19:59',
                'updated_at' => '2021-05-30 06:19:59',
            ),
            49 =>
            array (
                'id' => 65,
                'key' => 'add_product_categories',
                'table_name' => 'product_categories',
                'created_at' => '2021-05-30 06:19:59',
                'updated_at' => '2021-05-30 06:19:59',
            ),
            50 =>
            array (
                'id' => 66,
                'key' => 'delete_product_categories',
                'table_name' => 'product_categories',
                'created_at' => '2021-05-30 06:19:59',
                'updated_at' => '2021-05-30 06:19:59',
            ),
            51 =>
            array (
                'id' => 67,
                'key' => 'browse_menu_categories',
                'table_name' => 'menu_categories',
                'created_at' => '2021-05-30 06:21:46',
                'updated_at' => '2021-05-30 06:21:46',
            ),
            52 =>
            array (
                'id' => 68,
                'key' => 'read_menu_categories',
                'table_name' => 'menu_categories',
                'created_at' => '2021-05-30 06:21:46',
                'updated_at' => '2021-05-30 06:21:46',
            ),
            53 =>
            array (
                'id' => 69,
                'key' => 'edit_menu_categories',
                'table_name' => 'menu_categories',
                'created_at' => '2021-05-30 06:21:46',
                'updated_at' => '2021-05-30 06:21:46',
            ),
            54 =>
            array (
                'id' => 70,
                'key' => 'add_menu_categories',
                'table_name' => 'menu_categories',
                'created_at' => '2021-05-30 06:21:46',
                'updated_at' => '2021-05-30 06:21:46',
            ),
            55 =>
            array (
                'id' => 71,
                'key' => 'delete_menu_categories',
                'table_name' => 'menu_categories',
                'created_at' => '2021-05-30 06:21:46',
                'updated_at' => '2021-05-30 06:21:46',
            ),
            56 =>
            array (
                'id' => 72,
                'key' => 'browse_imperia_menus',
                'table_name' => 'imperia_menus',
                'created_at' => '2021-05-30 06:23:39',
                'updated_at' => '2021-05-30 06:23:39',
            ),
            57 =>
            array (
                'id' => 73,
                'key' => 'read_imperia_menus',
                'table_name' => 'imperia_menus',
                'created_at' => '2021-05-30 06:23:39',
                'updated_at' => '2021-05-30 06:23:39',
            ),
            58 =>
            array (
                'id' => 74,
                'key' => 'edit_imperia_menus',
                'table_name' => 'imperia_menus',
                'created_at' => '2021-05-30 06:23:39',
                'updated_at' => '2021-05-30 06:23:39',
            ),
            59 =>
            array (
                'id' => 75,
                'key' => 'add_imperia_menus',
                'table_name' => 'imperia_menus',
                'created_at' => '2021-05-30 06:23:39',
                'updated_at' => '2021-05-30 06:23:39',
            ),
            60 =>
            array (
                'id' => 76,
                'key' => 'delete_imperia_menus',
                'table_name' => 'imperia_menus',
                'created_at' => '2021-05-30 06:23:39',
                'updated_at' => '2021-05-30 06:23:39',
            ),
            61 =>
            array (
                'id' => 77,
                'key' => 'browse_products',
                'table_name' => 'products',
                'created_at' => '2021-05-30 06:31:05',
                'updated_at' => '2021-05-30 06:31:05',
            ),
            62 =>
            array (
                'id' => 78,
                'key' => 'read_products',
                'table_name' => 'products',
                'created_at' => '2021-05-30 06:31:05',
                'updated_at' => '2021-05-30 06:31:05',
            ),
            63 =>
            array (
                'id' => 79,
                'key' => 'edit_products',
                'table_name' => 'products',
                'created_at' => '2021-05-30 06:31:05',
                'updated_at' => '2021-05-30 06:31:05',
            ),
            64 =>
            array (
                'id' => 80,
                'key' => 'add_products',
                'table_name' => 'products',
                'created_at' => '2021-05-30 06:31:05',
                'updated_at' => '2021-05-30 06:31:05',
            ),
            65 =>
            array (
                'id' => 81,
                'key' => 'delete_products',
                'table_name' => 'products',
                'created_at' => '2021-05-30 06:31:05',
                'updated_at' => '2021-05-30 06:31:05',
            ),
            66 =>
            array (
                'id' => 82,
                'key' => 'browse_space_categories',
                'table_name' => 'space_categories',
                'created_at' => '2021-05-31 07:18:26',
                'updated_at' => '2021-05-31 07:18:26',
            ),
            67 =>
            array (
                'id' => 83,
                'key' => 'read_space_categories',
                'table_name' => 'space_categories',
                'created_at' => '2021-05-31 07:18:26',
                'updated_at' => '2021-05-31 07:18:26',
            ),
            68 =>
            array (
                'id' => 84,
                'key' => 'edit_space_categories',
                'table_name' => 'space_categories',
                'created_at' => '2021-05-31 07:18:26',
                'updated_at' => '2021-05-31 07:18:26',
            ),
            69 =>
            array (
                'id' => 85,
                'key' => 'add_space_categories',
                'table_name' => 'space_categories',
                'created_at' => '2021-05-31 07:18:26',
                'updated_at' => '2021-05-31 07:18:26',
            ),
            70 =>
            array (
                'id' => 86,
                'key' => 'delete_space_categories',
                'table_name' => 'space_categories',
                'created_at' => '2021-05-31 07:18:26',
                'updated_at' => '2021-05-31 07:18:26',
            ),
            71 =>
            array (
                'id' => 87,
                'key' => 'browse_spaces',
                'table_name' => 'spaces',
                'created_at' => '2021-05-31 07:22:29',
                'updated_at' => '2021-05-31 07:22:29',
            ),
            72 =>
            array (
                'id' => 88,
                'key' => 'read_spaces',
                'table_name' => 'spaces',
                'created_at' => '2021-05-31 07:22:29',
                'updated_at' => '2021-05-31 07:22:29',
            ),
            73 =>
            array (
                'id' => 89,
                'key' => 'edit_spaces',
                'table_name' => 'spaces',
                'created_at' => '2021-05-31 07:22:29',
                'updated_at' => '2021-05-31 07:22:29',
            ),
            74 =>
            array (
                'id' => 90,
                'key' => 'add_spaces',
                'table_name' => 'spaces',
                'created_at' => '2021-05-31 07:22:29',
                'updated_at' => '2021-05-31 07:22:29',
            ),
            75 =>
            array (
                'id' => 91,
                'key' => 'delete_spaces',
                'table_name' => 'spaces',
                'created_at' => '2021-05-31 07:22:29',
                'updated_at' => '2021-05-31 07:22:29',
            ),
            76 =>
            array (
                'id' => 92,
                'key' => 'browse_discount_categories',
                'table_name' => 'discount_categories',
                'created_at' => '2021-05-31 07:39:09',
                'updated_at' => '2021-05-31 07:39:09',
            ),
            77 =>
            array (
                'id' => 93,
                'key' => 'read_discount_categories',
                'table_name' => 'discount_categories',
                'created_at' => '2021-05-31 07:39:09',
                'updated_at' => '2021-05-31 07:39:09',
            ),
            78 =>
            array (
                'id' => 94,
                'key' => 'edit_discount_categories',
                'table_name' => 'discount_categories',
                'created_at' => '2021-05-31 07:39:09',
                'updated_at' => '2021-05-31 07:39:09',
            ),
            79 =>
            array (
                'id' => 95,
                'key' => 'add_discount_categories',
                'table_name' => 'discount_categories',
                'created_at' => '2021-05-31 07:39:09',
                'updated_at' => '2021-05-31 07:39:09',
            ),
            80 =>
            array (
                'id' => 96,
                'key' => 'delete_discount_categories',
                'table_name' => 'discount_categories',
                'created_at' => '2021-05-31 07:39:09',
                'updated_at' => '2021-05-31 07:39:09',
            ),
            81 =>
            array (
                'id' => 97,
                'key' => 'browse_discounts',
                'table_name' => 'discounts',
                'created_at' => '2021-05-31 07:42:26',
                'updated_at' => '2021-05-31 07:42:26',
            ),
            82 =>
            array (
                'id' => 98,
                'key' => 'read_discounts',
                'table_name' => 'discounts',
                'created_at' => '2021-05-31 07:42:26',
                'updated_at' => '2021-05-31 07:42:26',
            ),
            83 =>
            array (
                'id' => 99,
                'key' => 'edit_discounts',
                'table_name' => 'discounts',
                'created_at' => '2021-05-31 07:42:26',
                'updated_at' => '2021-05-31 07:42:26',
            ),
            84 =>
            array (
                'id' => 100,
                'key' => 'add_discounts',
                'table_name' => 'discounts',
                'created_at' => '2021-05-31 07:42:26',
                'updated_at' => '2021-05-31 07:42:26',
            ),
            85 =>
            array (
                'id' => 101,
                'key' => 'delete_discounts',
                'table_name' => 'discounts',
                'created_at' => '2021-05-31 07:42:26',
                'updated_at' => '2021-05-31 07:42:26',
            ),
            86 =>
            array (
                'id' => 102,
                'key' => 'browse_imperia_roles',
                'table_name' => 'imperia_roles',
                'created_at' => '2021-06-04 18:50:23',
                'updated_at' => '2021-06-04 18:50:23',
            ),
            87 =>
            array (
                'id' => 103,
                'key' => 'read_imperia_roles',
                'table_name' => 'imperia_roles',
                'created_at' => '2021-06-04 18:50:23',
                'updated_at' => '2021-06-04 18:50:23',
            ),
            88 =>
            array (
                'id' => 104,
                'key' => 'edit_imperia_roles',
                'table_name' => 'imperia_roles',
                'created_at' => '2021-06-04 18:50:23',
                'updated_at' => '2021-06-04 18:50:23',
            ),
            89 =>
            array (
                'id' => 105,
                'key' => 'add_imperia_roles',
                'table_name' => 'imperia_roles',
                'created_at' => '2021-06-04 18:50:23',
                'updated_at' => '2021-06-04 18:50:23',
            ),
            90 =>
            array (
                'id' => 106,
                'key' => 'delete_imperia_roles',
                'table_name' => 'imperia_roles',
                'created_at' => '2021-06-04 18:50:23',
                'updated_at' => '2021-06-04 18:50:23',
            ),
            91 =>
            array (
                'id' => 107,
                'key' => 'browse_imperia_users',
                'table_name' => 'imperia_users',
                'created_at' => '2021-06-04 18:52:04',
                'updated_at' => '2021-06-04 18:52:04',
            ),
            92 =>
            array (
                'id' => 108,
                'key' => 'read_imperia_users',
                'table_name' => 'imperia_users',
                'created_at' => '2021-06-04 18:52:04',
                'updated_at' => '2021-06-04 18:52:04',
            ),
            93 =>
            array (
                'id' => 109,
                'key' => 'edit_imperia_users',
                'table_name' => 'imperia_users',
                'created_at' => '2021-06-04 18:52:04',
                'updated_at' => '2021-06-04 18:52:04',
            ),
            94 =>
            array (
                'id' => 110,
                'key' => 'add_imperia_users',
                'table_name' => 'imperia_users',
                'created_at' => '2021-06-04 18:52:04',
                'updated_at' => '2021-06-04 18:52:04',
            ),
            95 =>
            array (
                'id' => 111,
                'key' => 'delete_imperia_users',
                'table_name' => 'imperia_users',
                'created_at' => '2021-06-04 18:52:04',
                'updated_at' => '2021-06-04 18:52:04',
            ),
        ));


    }
}
