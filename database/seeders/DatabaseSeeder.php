<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Src\Blog\Comment\Infrastructure\EloquentModels\CommentEloquentModel;
use Src\Auth\Infrastructure\EloquentModels\UserEloquentModel;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        UserEloquentModel::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        CommentEloquentModel::factory(20)->create();
    }
}
