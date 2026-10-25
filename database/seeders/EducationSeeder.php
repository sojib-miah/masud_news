<?php

namespace Database\Seeders;

use App\Models\Education;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
        "এসএসসি",
        "এইচএসসি",
        "ডিপ্লোমা",
        "স্নাতক",
        "স্নাতকোত্তর",
        "পিএইচডি",
        "অন্যান্য",
    ];

    foreach ($levels as $level) {
        Education::create(['name' => $level]);
    }

    }
}
