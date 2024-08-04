<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition()
    {
        return [
            'user_owner' => 6, // Giả sử user_owner có ID từ 1 đến 10
            'user_other' => $this->faker->numberBetween(1, 10), // Giả sử user_other có ID từ 101 đến 20
            'last_message_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
