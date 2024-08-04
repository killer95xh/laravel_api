<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'conversation_id' => $this->faker->randomElement([10]), // Giả sử có 50 cuộc trò chuyện
            'from_user_id' => $this->faker->randomElement([6, 7]), // Giả sử có 10 user
            'message' => $this->faker->text,
            'is_seen' => $this->faker->randomElement([0]),
            'is_delete_by' => $this->faker->randomElement([0]),
        ];
    }
}
