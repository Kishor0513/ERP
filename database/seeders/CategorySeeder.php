<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Felt Balls' => [
                'Solid', 'Swirl', 'Polka Dot', 'Glitter', 'Tie-Dye', 'Oval', 'Stone Shape',
            ],
            'Craft Supplies' => [
                'Hearts, Stars & Rainbows', 'Food, Fruit & Veggies', 'Animals & Characters',
                'Sports Balls', 'Alphabets & Numbers', 'Felt Fabric Sheets',
                'Other Shapes', 'DIY Essentials',
            ],
            'Seasonal' => [
                'Halloween', 'Christmas', 'Easter', "St. Patrick's & 4th of July",
                'Thanksgiving', 'Fall', "Valentine's",
            ],
            'Felt Shoes' => [
                'Wool Boots', 'Ankle Boots', 'Slippers',
            ],
            'Pet Products' => [
                'Cat Houses & Caves - Standard', 'Cat Houses & Caves - Premium',
                'Dog Beds', 'Pet Toys', 'Cat Mats',
            ],
            'Wool Dryer Balls' => [],
            'Decor' => [
                'Trivets & Coasters', 'Stone Pouf & Stool', 'Chair Pads',
                'Baskets', 'Flowers & Plants', 'Cushions',
                'Mobile Hangers', 'Wreaths', 'Tree Skirts', 'Garlands',
            ],
            'Rugs' => [
                'Round', 'Rectangular', 'Stone & Pebble', 'Sheet', 'Special Design',
            ],
            'Yarns' => [
                'Felted Wool', 'Banana Fiber', 'Recycled Silk', 'Nettle', 'Merino', 'Handspun',
            ],
            'Fashion' => [
                'Merino Scarves', 'Felt Bags & Purses',
            ],
            'Yoga Mats' => [],
        ];

        $order = 0;
        foreach ($categories as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'sort_order' => $order++,
                'is_active' => true,
                'description' => "Premium handcrafted {$parentName} from Nepal",
            ]);

            foreach ($children as $index => $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'sort_order' => $index,
                    'is_active' => true,
                    'description' => "Handcrafted {$childName} made from 100% NZ wool",
                ]);
            }
        }
    }
}
