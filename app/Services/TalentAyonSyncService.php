<?php

namespace App\Services;

use App\Enums\TalentStatus;
use App\Models\Talent;
use App\Services\Contracts\TalentAyonSyncServiceInterface;
use Benjamin\AyonConnector\Contracts\AyonClientInterface;

/**
 * Service responsible for syncing Talents with AYON.
 */
class TalentAyonSyncService implements TalentAyonSyncServiceInterface
{
    /**
     * @param AyonClientInterface $ayonClient The AYON client implementation
     */
    public function __construct(private readonly AyonClientInterface $ayonClient) {}

    /**
     * Sync a Talent to AYON.
     *
     * Handles creation for new talents, updates for existing ones,
     * and deactivation if required.
     *
     * @param Talent $talent The talent to sync
     * @param bool $isNew Whether this is a newly created talent
     * @param string|null $newStatus Optional new status to override the current AYON status
     *
     * @return void
     *
     * @phpstan-param TalentStatus::*|null $newStatus
     */
    public function sync(Talent $talent, bool $isNew = false, ?string $newStatus = null): void
    {
        if ($isNew) {
            $talent->setAyonName();
            $this->ayonClient->createUser($talent->toAyonUser(), $talent->ayon_name);
            return;
        }

        $status = $newStatus ?? $talent->ayon_sync_status;

        if ($status === TalentStatus::INACTIVE->value) {
            $this->ayonClient->deactivateUser($talent->ayon_name);
            return;
        }

        $this->ayonClient->updateUser($talent->toAyonUser(), $talent->ayon_name);
    }

    /**
     * Delete a Talent from AYON.
     *
     * @param Talent $talent The talent to delete
     *
     * @return void
     */
    public function delete(Talent $talent): void
    {
        if ($talent->ayon_name) {
            $this->ayonClient->deleteUser($talent->ayon_name);
        }
    }
}
