<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Blog\Comment\Infrastructure\EloquentModels\CommentEloquentModel;

/**
 * @extends Factory<CommentEloquentModel>
 */
class CommentFactory extends Factory
{   
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => fake()->optional()->numberBetween(1, 10), // 隨機產生 1~10 或 null
            'message' => fake()->realText(50), // 生成一段測試文字
        ];
    }
}
