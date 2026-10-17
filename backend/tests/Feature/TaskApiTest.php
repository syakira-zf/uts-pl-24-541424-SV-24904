<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_cannot_be_accessed_without_token(): void
    {
        $response = $this->withHeader(
            'Accept',
            'application/json'
        )->getJson('/api/tasks');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }

    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'Mengerjakan UTS KEPL',
            'description' => 'Membuat aplikasi SyakiraTask',
            'type' => 'task',
            'activity_date' => '2026-10-17',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'deadline' => '2026-10-17 23:59:00',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath(
                     'data.title',
                     'Mengerjakan UTS KEPL'
                 );

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Mengerjakan UTS KEPL',
        ]);
    }

    public function test_invalid_task_returns_422(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => '',
            'type' => 'invalid',
            'activity_date' => 'bukan-tanggal',
            'priority' => 'super',
            'status' => 'unknown',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'title',
                     'type',
                     'activity_date',
                     'priority',
                     'status',
                 ]);
    }
}
