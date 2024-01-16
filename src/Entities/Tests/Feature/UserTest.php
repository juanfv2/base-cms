<?php

namespace Tests\Unit;

use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user()
    {
        $type = User::factory()->create(['id' => 1]);

        $this->assertEquals(1, $type->id);
    }
}
