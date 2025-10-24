<?php

namespace App\Console\Commands;

use App\Enums\TalentStatus;
use App\Models\Talent;
use App\Services\Contracts\TalentAyonSyncServiceInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TrackerSyncTalents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production-tracker:sync-talents
                            {--status= : Filter talents by status (pending, inactive, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize talents with AYON. Can resync all or filtered by status.';

    private TalentAyonSyncServiceInterface $ayonSync;

    /**
     * Create a new command instance.
     *
     * @param TalentAyonSyncServiceInterface $ayonSync
     */
    public function __construct(TalentAyonSyncServiceInterface $ayonSync)
    {
        parent::__construct();
        $this->ayonSync = $ayonSync;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $statusOption = $this->option('status');

        $query = Talent::query();

        if ($statusOption && $statusOption !== 'all') {
            if (!in_array($statusOption, array_column(TalentStatus::cases(), 'value'))) {
                $this->error("Invalid status '{$statusOption}'. Valid: pending, inactive, synced.");
                return 1;
            }
            $query->where('ayon_sync_status', $statusOption);
        }

        $talents = $query->get();

        if ($talents->isEmpty()) {
            $this->info('No talents found for the given filter.');
            return 0;
        }

        foreach ($talents as $talent) {
            DB::beginTransaction();
            try {
                $isNew = is_null($talent->ayon_name);
                $newStatus = TalentStatus::SYNCED->value;
                $this->ayonSync->sync($talent, $isNew, $newStatus);

                $talent->markAsSynced();
                DB::commit();

                $this->info("Talent {$talent->id} ({$talent->first_name} {$talent->last_name}) synced successfully.");
            } catch (Exception $e) {
                DB::rollBack();
                $this->error("Failed to sync Talent {$talent->id} ({$talent->first_name} {$talent->last_name}): {$e->getMessage()}");
            }
        }

        $this->info('Talents sync complete.');
        return 0;
    }
}
