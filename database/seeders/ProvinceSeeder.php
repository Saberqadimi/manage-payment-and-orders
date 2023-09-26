<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        $sqlFile = __DIR__ . '/adm_provinces.sql';
        $sql = file_get_contents($sqlFile);
        DB::unprepared($sql);
    }
}
