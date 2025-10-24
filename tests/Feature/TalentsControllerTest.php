<?php

namespace Tests\Feature;

use App\Enums\TalentRole;
use App\Models\Talent;
use Benjamin\AyonConnector\Contracts\AyonClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class TalentsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $ayonClientMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ayonClientMock = Mockery::mock(AyonClientInterface::class);
        $this->app->instance(AyonClientInterface::class, $this->ayonClientMock);
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
    }

    public function test_store_creates_talent_and_syncs_with_ayon()
    {
        $this->ayonClientMock->shouldReceive('createUser')->once();

        $response = $this->post(route('talents.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role' => TalentRole::DEVELOPER->value,
        ]);

        $talent = Talent::first();
        $talent->setAyonName();

        $response->assertRedirect(route('talents.index'));
        $this->assertNotNull($talent->ayon_name);
        $this->assertEquals('synced', $talent->ayon_sync_status);
    }

    public function test_update_calls_ayon_update()
    {
        $talent = Talent::factory()->create([
            'role' => TalentRole::DEVELOPER->value,
        ]);
        $talent->setAyonName();

        $this->ayonClientMock->shouldReceive('updateUser')->once();

        $response = $this->put(route('talents.update', $talent->id), [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'role' => TalentRole::DEVELOPER->value,
        ]);

        $response->assertRedirect(route('talents.index'));
        $this->assertEquals('Jane', $talent->fresh()->first_name);
    }

    public function test_destroy_calls_ayon_delete()
    {
        $talent = Talent::factory()->create([
            'role' => TalentRole::DEVELOPER->value,
        ]);
        $talent->setAyonName();

        $this->ayonClientMock->shouldReceive('deleteUser')->with($talent->ayon_name)->once();

        $response = $this->delete(route('talents.destroy', $talent->id));

        $response->assertRedirect(route('talents.index'));
        $this->assertDatabaseMissing('talents', ['id' => $talent->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
