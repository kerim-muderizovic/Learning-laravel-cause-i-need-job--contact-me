<?php

namespace Database\Seeders;

use App\Models\admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $adminUsers=User::where('role','admin')->get();
        foreach($adminUsers as $Au )
        {
            admin::firstOrCreate(['id' => $Au->id ]);
        }
    }
}
