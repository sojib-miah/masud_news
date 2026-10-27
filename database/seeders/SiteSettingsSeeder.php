<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert default site settings into the database
        DB::table('site_settings')->insert([
            'logo' => null,
            'favaicon' => null,
            'docs' => null,
            'inv_termes' => null,
            'tax' => null,
            'inv_prefix' => null,
            'company_location' => null,
            'company_contact' => null,
            'company_email' => null,
            'company_web' => null,
            'company_facebook' => null,
            'company_youtube' => null,
            'company_linkdin' => null,
            'site_termes' => null,
            'site_about' => null,
            'register_status' => 0,
            'news_view' => 0,
            'create_news' => 0,
            'currency' => 'BDT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
