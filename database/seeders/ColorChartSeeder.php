<?php

namespace Database\Seeders;

use App\Models\ColorChartEntry;
use Illuminate\Database\Seeder;

class ColorChartSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['code' => 'NW', 'name' => 'Natural White', 'hex_color' => '#FAFAF5'],
            ['code' => 'IC', 'name' => 'Ivory/Cream', 'hex_color' => '#FFFFF0'],
            ['code' => 'CM', 'name' => 'Camel', 'hex_color' => '#C19A6B'],
            ['code' => 'TN', 'name' => 'Tan', 'hex_color' => '#D2B48C'],
            ['code' => 'MT', 'name' => 'Marbled Tan', 'hex_color' => '#C8AD7F'],
            ['code' => 'LB', 'name' => 'Light Brown', 'hex_color' => '#A0785A'],
            ['code' => 'CH', 'name' => 'Chocolate', 'hex_color' => '#5C3A21'],
            ['code' => 'CC', 'name' => 'Charcoal', 'hex_color' => '#36454F'],
            ['code' => 'DG', 'name' => 'Dark Grey', 'hex_color' => '#555555'],
            ['code' => 'LG', 'name' => 'Light Grey', 'hex_color' => '#B0B0B0'],
            ['code' => 'BK', 'name' => 'Black', 'hex_color' => '#1A1A1A'],
            ['code' => 'RD', 'name' => 'Red', 'hex_color' => '#CC0000'],
            ['code' => 'CR', 'name' => 'Crimson', 'hex_color' => '#DC143C'],
            ['code' => 'OR', 'name' => 'Orange', 'hex_color' => '#FF6600'],
            ['code' => 'MY', 'name' => 'Mustard Yellow', 'hex_color' => '#DAA520'],
            ['code' => 'OG', 'name' => 'Olive Green', 'hex_color' => '#6B8E23'],
            ['code' => 'FG', 'name' => 'Forest Green', 'hex_color' => '#228B22'],
            ['code' => 'SB', 'name' => 'Sky Blue', 'hex_color' => '#87CEEB'],
            ['code' => 'NB', 'name' => 'Navy Blue', 'hex_color' => '#000080'],
            ['code' => 'LV', 'name' => 'Lavender', 'hex_color' => '#B57EDC'],
            ['code' => 'PL', 'name' => 'Purple', 'hex_color' => '#800080'],
            ['code' => 'PK', 'name' => 'Pink', 'hex_color' => '#FF69B4'],
            ['code' => 'BP', 'name' => 'Blush Pink', 'hex_color' => '#FFB7C5'],
            ['code' => 'SL', 'name' => 'Salmon', 'hex_color' => '#FA8072'],
            ['code' => 'TL', 'name' => 'Teal', 'hex_color' => '#008080'],
            ['code' => 'TQ', 'name' => 'Turquoise', 'hex_color' => '#40E0D0'],
        ];

        foreach ($colors as $color) {
            ColorChartEntry::create($color);
        }
    }
}
