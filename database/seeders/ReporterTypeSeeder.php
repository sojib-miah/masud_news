<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReporterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    $types = [
        "ইন্টার্ন রিপোর্টার",
        "জুনিয়র রিপোর্টার",
        "সিটি রিপোর্টার",
        "পৌর রিপোর্টার",
        "ওয়ার্ড প্রতিনিধি",
        "ইউনিয়ন প্রতিনিধি",
        "উপজেলা প্রতিনিধি",
        "জেলা প্রতিনিধি",
        "বিভাগীয় প্রতিনিধি",
        "বিশেষ প্রতিনিধি",
        "ক্যাম্পাস প্রতিনিধি",
        "স্টাফ রিপোর্টার",
        "ক্রাইম রিপোর্টার",
        "অনুসন্ধানী সাংবাদিক",
        "ফটো সাংবাদিক",
        "ভিডিও সাংবাদিক",
        "অনলাইন প্রতিবেদক",
        "খেলাধুলা প্রতিবেদক",
        "অর্থনীতি প্রতিবেদক",
        "ধর্ম ও সমাজ প্রতিবেদক",
        "সংস্কৃতি ও বিনোদন প্রতিবেদক",
        "পরিবেশ ও জলবায়ু প্রতিবেদক",
        "শিক্ষা প্রতিবেদক",
        "স্বাস্থ্য প্রতিবেদক",
    ];

    foreach ($types as $type) {
        \App\Models\ReporterType::create(['name' => $type]);
    }
}
}
