<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // database/factories/PlayerFactory.php のデータを10件作成する
        Player::factory()->count(10)->create();
    }
}
