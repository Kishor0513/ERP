<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Size' => ['2cm', '3cm', '4cm', '5cm', '6cm', '8cm', '10cm', '15cm', '20cm', '25cm', '30cm', '40cm', '50cm', '60cm', '80cm', '100cm'],
            'Color' => [],  // Colors are managed via ColorChartEntry
            'Package/Pack Count' => ['1 piece', '5 pack', '10 pack', '20 pack', '50 pack', '100 pack', '200 pack', '500 pack'],
            'Design Pattern' => ['Solid', 'Swirl', 'Polka Dot', 'Glitter', 'Tie-Dye', 'Striped', 'Marbled', 'Ombre', 'Natural'],
        ];

        foreach ($attributes as $name => $values) {
            $attribute = Attribute::create(['name' => $name]);

            foreach ($values as $value) {
                AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $value,
                ]);
            }
        }
    }
}
