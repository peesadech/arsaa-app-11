<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'flag' => "\u{1F1FA}\u{1F1F8}",
                'direction' => 'ltr',
                'is_default' => true,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'code' => 'th',
                'name' => 'Thai',
                'native_name' => 'ภาษาไทย',
                'flag' => "\u{1F1F9}\u{1F1ED}",
                'direction' => 'ltr',
                'is_default' => false,
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'code' => 'zh',
                'name' => 'Chinese',
                'native_name' => '中文',
                'flag' => "\u{1F1E8}\u{1F1F3}",
                'direction' => 'ltr',
                'is_default' => false,
                'status' => 1,
                'sort_order' => 3,
            ],
        ];

        foreach ($languages as $lang) {
            Language::updateOrCreate(
                ['code' => $lang['code']],
                $lang
            );
        }
    }
}
