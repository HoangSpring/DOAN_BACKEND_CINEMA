<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Hành động', 'slug' => 'hanh-dong'],
            ['name' => 'Khoa học viễn tưởng', 'slug' => 'khoa-hoc-vien-tuong'],
            ['name' => 'Tâm lý', 'slug' => 'tam-ly'],
            ['name' => 'Đề cử Oscar', 'slug' => 'de-cu-oscar'],
            ['name' => 'Hoạt hình', 'slug' => 'hoat-hinh'],
            ['name' => 'Đang hot', 'slug' => 'dang-hot'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
