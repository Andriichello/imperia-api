<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ServiceCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('service_categories')->delete();

        \DB::table('service_categories')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Entertainment',
                'description' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Catering',
                'description' => NULL,
            ),
        ));


    }
}
