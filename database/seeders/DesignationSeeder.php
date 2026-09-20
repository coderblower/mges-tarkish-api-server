<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            // Existing agricultural & fishery designations
            'Fishermen',
            'General Fisherman',
            'Boat Operators / Skippers',
            'Net Makers',
            'Net Repairers',
            'Divers',
            'Fish Farmers',
            'Hatchery Workers',
            'Aquaculture Technicians',
            'Aquaculture labour',
            'Fish Processors',
            'Dry Fish Workers',
            'Packaging Workers',
            'Cold Storage Workers',
            'Farmers / Cultivators',
            'Agricultural Laborers',
            'Agriculture helpers',
            'Irrigation Workers',
            'Dairy Farmers',
            'Poultry Farmers',
            'Livestock Farmers',
            'Food Processing Workers',
            'Packers / Sorters',
            'Transport Workers',
            'Agricultural Equipment Operators',
            'Fertilizer & Pesticide Dealers',
            'Nursery Workers',
            'Animal Rearing & Farm Manager',
            'Calf Rearers',
            'Shepard',
            'Cattle herder',
            'Butcher',

            // New designations from Image
            'Supervisor',
            'Blaster & Painter',
            'Pipe & Outfitting',
            'Hull Fabrication',
            'Mechanic',
            'Electrician',
            'Scaffolder/ Staggers',
            'Skilled Technician',
            'Semi Skilled worker',
            'Supervisor Pipe & Outfitting',
            'Supervisor Mechanical',
            'Skilled Machinist/ Fitter',
            'Cable puller',
            'Carpenter',
            'Mason',
            'Welder/ Fitter',
            'Industrial Helper',
            'General cleaner',
            'Crane Rigger',
            'Crane Operator',
            'Heavy Duty Driver',
            'Utility Electrician',
            'Utility Plumber',
            'Landscape Helper',

            // New designations from Text List
            '3G Welder',
            '6G & TIG Welder',
            'Rigger',
            '4G & Welder',
            'Welder & Flame Cutter',
            'Mobile Crane Operator',
            'Forklift Operator',
            'Forkit Operator',
            'Drum Truck Driver',
            'Excavator Operator',
            'Tiles Mason',
            'S/Carpenter',
        ];

        foreach ($designations as $name) {
            Designation::firstOrCreate(['name' => $name]);
        }
    }
}
