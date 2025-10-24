<?php

namespace App\Enums;

enum TalentRole: string
{
    case DEVELOPER = 'developer';
    case DESIGNER = 'designer';
    case CG_SUPERVISOR = 'cg_supervisor';
    case CG_ARTIST = 'cg_artist';

    public function label(): string
    {
        return match($this) {
            self::DEVELOPER => 'Developer',
            self::DESIGNER => 'Designer',
            self::CG_SUPERVISOR => 'CG Supervisor',
            self::CG_ARTIST => 'CG Artist',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            self::cases()
        );
    }
}
