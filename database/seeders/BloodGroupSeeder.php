<?php

namespace Database\Seeders;

use App\Models\BloodGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BloodGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = ["এ+", "এ-", "বি+", "বি-", "এবি+", "এবি-", "ও+", "ও-"];
        foreach ($groups as $group) {
            BloodGroup::create(['name' => $group]);
        }
    }
}
