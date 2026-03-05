<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pet;
use App\Models\WeightLog;
use App\Models\MedicalRecord;
use App\Models\Reminder;
use App\Models\Article;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users
        $users = User::factory(5)->create();

        // For each user, create pets and related data
        $users->each(function ($user) {
            $pets = Pet::factory(rand(1, 3))->for($user)->create();

            // For each pet, create weight logs, medical records, and reminders
            $pets->each(function ($pet) {
                WeightLog::factory(rand(2, 5))->for($pet)->create();
                MedicalRecord::factory(rand(1, 3))->for($pet)->create();
                Reminder::factory(rand(1, 4))->for($pet)->create();
            });

            // Create articles for the user
            Article::factory(rand(1, 2))->for($user, 'author')->create();
        });

        // Create standalone services
        Service::factory(10)->create();
    }
}
