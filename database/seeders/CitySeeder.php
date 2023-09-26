<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{

    public function run()
    {
        $sqlFile = __DIR__ . '/adm_cities.sql';
        $sql = file_get_contents($sqlFile);
        DB::unprepared($sql);
    }

}
