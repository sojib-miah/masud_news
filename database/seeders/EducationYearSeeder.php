<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $start = 1971;
        $end = now()->year;

        for ($year = $start; $year <= $end; $year++) {
            $banglaYear = strtr($year, [
                '0' => '০',
                '1' => '১',
                '2' => '২',
                '3' => '৩',
                '4' => '৪',
                '5' => '৫',
                '6' => '৬',
                '7' => '৭',
                '8' => '৮',
                '9' => '৯',
            ]);

            \App\Models\EducationYear::create(['year' => $banglaYear]);
        }
    }
}
