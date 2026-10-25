<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReporterAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $areas = [
            "বিভাগ",
            "জেলা",
            "উপজেলা",
            "পৌরসভা",
            "ইউনিয়ন",
        ];

        foreach ($areas as $area) {
            \App\Models\ReporterArea::create(['name' => $area]);
        }
    }
}
