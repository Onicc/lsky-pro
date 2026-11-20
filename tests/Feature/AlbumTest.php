<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    public function test_album_intro_can_be_cleared_and_api_deletion_updates_count(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->postJson('/user/albums', ['name' => 'Sample', 'intro' => 'Old intro'])
            ->assertOk()->assertJsonPath('status', true);
        $id = $owner->albums()->value('id');
        $this->putJson('/user/albums/'.$id, ['name' => 'Sample', 'intro' => ''])
            ->assertOk()->assertJsonPath('status', true);
        $this->assertSame('', $owner->albums()->whereKey($id)->value('intro'));
        $this->assertSame(1, $owner->fresh()->album_num);

        $token = $owner->createToken('album-test')->plainTextToken;
        $this->withToken($token)->deleteJson('/api/v1/albums/'.$id)
            ->assertOk()->assertJsonPath('status', true);
        $this->assertSame(0, $owner->fresh()->album_num);
    }
}
