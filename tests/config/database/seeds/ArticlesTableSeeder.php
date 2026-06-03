<?php

namespace Backpack\CRUD\Tests\Config\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('articles')->insert([[
            'id' => 1,
            'user_id' => 1,
            'content' => 'Some Content',
            'metas' => '{"meta_title":"Meta Title Value","meta_description":"Meta Description Value"}',
            'tags' => '{"tags":["tag1","tag2","tag3"]}',
            'extras' => '{"extra_details":["detail1","detail2","detail3"]}',
            'cast_metas' => '{"cast_meta_title":"Meta Title Value","cast_meta_description":"Meta Description Value"}',
            'cast_tags' => '{"cast_tags":["tag1","tag2","tag3"]}',
            'cast_extras' => '{"cast_extra_details":["detail1","detail2","detail3"]}',
        ]]);
    }
}
