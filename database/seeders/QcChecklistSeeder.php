<?php

namespace Database\Seeders;

use App\Models\QcChecklist;
use Illuminate\Database\Seeder;

class QcChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $checklists = [
            [
                'category_id' => 2,
                'name' => 'Felt Balls Quality Checklist',
                'criteria' => json_encode([
                    ['item' => 'Shape Consistency', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Size Tolerance (±1mm)', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Color Consistency', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'No Loose Fibers', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Firmness Test', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Surface Smoothness', 'type' => 'pass_fail', 'required' => false],
                ]),
                'is_active' => true,
            ],
            [
                'category_id' => 32,
                'name' => 'Cat Caves Quality Checklist',
                'criteria' => json_encode([
                    ['item' => 'Seam Strength', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Size Accuracy (±2cm)', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Surface Quality', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Structural Integrity', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Entrance Size', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'No Defects or Tears', 'type' => 'pass_fail', 'required' => true],
                ]),
                'is_active' => true,
            ],
            [
                'category_id' => 28,
                'name' => 'Shoes Quality Checklist',
                'criteria' => json_encode([
                    ['item' => 'Sole Grip', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Stitching Quality', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Sizing Accuracy', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Symmetry Check', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Comfort Assessment', 'type' => 'pass_fail', 'required' => false],
                    ['item' => 'Water Resistance', 'type' => 'pass_fail', 'required' => false],
                ]),
                'is_active' => true,
            ],
            [
                'category_id' => null,
                'name' => 'General Product Quality Checklist',
                'criteria' => json_encode([
                    ['item' => 'Overall Appearance', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Packaging Quality', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Labeling Accuracy', 'type' => 'pass_fail', 'required' => true],
                    ['item' => 'Weight Check', 'type' => 'pass_fail', 'required' => false],
                    ['item' => 'Odor Check', 'type' => 'pass_fail', 'required' => false],
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($checklists as $checklist) {
            QcChecklist::create($checklist);
        }
    }
}
