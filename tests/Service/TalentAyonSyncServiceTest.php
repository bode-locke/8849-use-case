<?php

namespace Tests\Unit\Services;

use App\Enums\TalentRole;
use App\Enums\TalentStatus;
use App\Models\Talent;
use App\Services\TalentAyonSyncService;
use Benjamin\AyonConnector\Contracts\AyonClientInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TalentAyonSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private TalentAyonSyncService $service;
    private MockInterface $ayonClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ayonClientMock = Mockery::mock(AyonClientInterface::class);
        $this->service = new TalentAyonSyncService($this->ayonClientMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_sync_a_new_talent_to_ayon(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => null,
            'ayon_sync_status' => TalentStatus::PENDING->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('createUser')
            ->once()
            ->withArgs(function ($userData, $ayonName) use ($talent) {
                return $userData['attrib']['email'] === $talent->email
                    && $ayonName === 'JohnDoe';
            });

        $this->service->sync($talent, isNew: true);

        $talent->refresh();
        $this->assertEquals('JohnDoe', $talent->ayon_name);
    }

    #[Test]
    public function it_can_update_an_existing_talent_in_ayon(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'role' => TalentRole::DESIGNER,
            'ayon_name' => 'JaneSmith',
            'ayon_sync_status' => TalentStatus::SYNCED->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('updateUser')
            ->once()
            ->withArgs(function ($userData, $ayonName) use ($talent) {
                return $userData['attrib']['email'] === $talent->email
                    && $userData['attrib']['fullName'] === 'Jane Smith'
                    && $ayonName === 'JaneSmith';
            });

        $this->service->sync($talent, isNew: false);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_can_deactivate_a_talent_in_ayon(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'email' => 'mike.johnson@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => 'MikeJohnson',
            'ayon_sync_status' => TalentStatus::SYNCED->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('deactivateUser')
            ->once()
            ->with('MikeJohnson');

        $this->service->sync($talent, isNew: false, newStatus: TalentStatus::INACTIVE->value);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_deactivates_talent_when_current_status_is_inactive(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Sarah',
            'last_name' => 'Williams',
            'email' => 'sarah.williams@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => 'SarahWilliams',
            'ayon_sync_status' => TalentStatus::INACTIVE->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('deactivateUser')
            ->once()
            ->with('SarahWilliams');

        $this->service->sync($talent, isNew: false);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_can_delete_a_talent_from_ayon(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Tom',
            'last_name' => 'Brown',
            'email' => 'tom.brown@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => 'TomBrown',
            'ayon_sync_status' => TalentStatus::SYNCED->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('deleteUser')
            ->once()
            ->with('TomBrown');

        $this->service->delete($talent);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_does_not_call_ayon_delete_when_ayon_name_is_null(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Emily',
            'last_name' => 'Davis',
            'email' => 'emily.davis@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => null,
            'ayon_sync_status' => TalentStatus::PENDING->value,
        ]);

        $this->ayonClientMock
            ->shouldNotReceive('deleteUser');

        $this->service->delete($talent);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_sets_ayon_name_correctly_for_new_talents(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Robert',
            'last_name' => 'Taylor',
            'email' => 'robert.taylor@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => null,
        ]);

        $this->ayonClientMock
            ->shouldReceive('createUser')
            ->once();

        $this->service->sync($talent, isNew: true);

        $talent->refresh();
        $this->assertEquals('RobertTaylor', $talent->ayon_name);
    }

    #[Test]
    public function it_handles_names_with_spaces_correctly(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Jean Claude',
            'last_name' => 'Van Damme',
            'email' => 'jc.vandamme@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => null,
        ]);

        $this->ayonClientMock
            ->shouldReceive('createUser')
            ->once()
            ->withArgs(function ($userData, $ayonName) {
                return $ayonName === 'JeanClaudeVanDamme'
                    && $userData['attrib']['fullName'] === 'Jean Claude Van Damme';
            });

        $this->service->sync($talent, isNew: true);

        $talent->refresh();
        $this->assertEquals('JeanClaudeVanDamme', $talent->ayon_name);
    }

    #[Test]
    public function it_passes_correct_user_data_structure_to_ayon(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Cooper',
            'email' => 'alice.cooper@example.com',
            'role' => TalentRole::DESIGNER,
            'ayon_name' => 'AliceCooper',
        ]);

        $expectedStructure = [
            'attrib' => [
                'name' => 'AliceCooper',
                'fullName' => 'Alice Cooper',
                'email' => 'alice.cooper@example.com',
                'data' => [
                    'role' => TalentRole::DESIGNER,
                ],
            ],
        ];

        $this->ayonClientMock
            ->shouldReceive('updateUser')
            ->once()
            ->with($expectedStructure, 'AliceCooper');

        $this->service->sync($talent, isNew: false);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_prioritizes_new_status_over_current_status(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'David',
            'last_name' => 'Miller',
            'email' => 'david.miller@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => 'DavidMiller',
            'ayon_sync_status' => TalentStatus::SYNCED->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('deactivateUser')
            ->once()
            ->with('DavidMiller');

        $this->service->sync($talent, isNew: false, newStatus: TalentStatus::INACTIVE->value);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_updates_when_status_is_error(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Lisa',
            'last_name' => 'Anderson',
            'email' => 'lisa.anderson@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => 'LisaAnderson',
            'ayon_sync_status' => TalentStatus::ERROR->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('updateUser')
            ->once()
            ->with(Mockery::any(), 'LisaAnderson');

        $this->service->sync($talent, isNew: false);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_updates_when_status_is_pending(): void
    {
        $talent = Talent::factory()->create([
            'first_name' => 'Chris',
            'last_name' => 'Martinez',
            'email' => 'chris.martinez@example.com',
            'role' => TalentRole::DEVELOPER,
            'ayon_name' => 'ChrisMartinez',
            'ayon_sync_status' => TalentStatus::PENDING->value,
        ]);

        $this->ayonClientMock
            ->shouldReceive('updateUser')
            ->once()
            ->with(Mockery::any(), 'ChrisMartinez');

        $this->service->sync($talent, isNew: false);

        $this->assertTrue(true);
    }
}
