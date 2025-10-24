<?php

namespace App\Services\Contracts;

use App\Models\Talent;

interface TalentAyonSyncServiceInterface
{
    /**
     * Create or update a talent in AYON based on status.
     */
    public function sync(Talent $talent, bool $isNew, ?string $newStatus = null): void;

    /**
     * Delete a talent from AYON.
     */
    public function delete(Talent $talent): void;
}
