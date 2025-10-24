<?php

namespace App\Models;

use App\Enums\TalentStatus;
use App\Enums\TalentRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Talent
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property TalentRole $role
 * @property string|null $ayon_name
 * @property string|null $ayon_sync_status
 * @property \Illuminate\Support\Carbon|null $ayon_synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Talent extends Model
{
    /** @use HasFactory<\Database\Factories\TalentFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'talents';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'role',
        'ayon_name',
        'ayon_sync_status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => TalentRole::class,
            'ayon_synced_at' => 'datetime',
        ];
    }

    /**
     * Convert Talent instance to AYON API user array.
     *
     * @return array<string, mixed>
     */
    public function toAyonUser(): array
    {
        return [
            'attrib' => [
                'name' => $this->ayon_name,
                'fullName' => $this->getFullName(),
                'email' => $this->email,
                'data' => [
                    'role' => $this->role,
                ],
            ],
        ];
    }

    /**
     * Set the AYON username (ayon_name) based on first and last name.
     *
     * @return void
     */
    public function setAyonName(): void
    {
        $this->ayon_name = str_replace(' ', '', $this->getFullName());
        $this->save();
    }

    /**
     * Get full name of the talent.
     *
     * @return string
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Mark talent as pending for AYON sync.
     *
     * @return void
     */
    public function markAsPending(): void
    {
        $this->ayon_sync_status = TalentStatus::PENDING->value;
        $this->save();
    }

    /**
     * Mark talent as successfully synced with AYON.
     *
     * @return void
     */
    public function markAsSynced(): void
    {
        $this->ayon_sync_status = TalentStatus::SYNCED->value;
        $this->save();
    }

    /**
     * Mark talent as inactive.
     *
     * @return void
     */
    public function markAsInactive(): void
    {
        $this->ayon_sync_status = TalentStatus::INACTIVE->value;
        $this->save();
    }

    /**
     * Mark talent sync status as error.
     *
     * @return void
     */
    public function markAsError(): void
    {
        $this->ayon_sync_status = TalentStatus::ERROR->value;
        $this->save();
    }
}
