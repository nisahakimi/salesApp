<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,  // Random product name
            'description' => $this->faker->sentence,  // Random product description
            'price' => $this->faker->randomFloat(2, 10, 1000),  // Random price between 10 and 1000
            'stock' => $this->faker->numberBetween(1, 100),
        ];
    }
}
