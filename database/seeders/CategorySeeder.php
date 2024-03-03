<?php

namespace Database\Seeders;

use App\Traits\Helpers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    use Helpers;

    public function run(): void
    {
        // Fetch "Active" status id
        $status_id = CategorySeeder::getStatusId('Active');

        // array of categories
        $categories = [
            ['name' => 'Electronics', 'status_id' => $status_id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laptop', 'status_id' => $status_id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desktop', 'status_id' => $status_id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Android', 'status_id' => $status_id, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Iphone', 'status_id' => $status_id, 'created_at' => now(), 'updated_at' => now()],
        ];

        //insert the array of categories into the categories table
        DB::table('categories')->insert($categories);
    }
}
