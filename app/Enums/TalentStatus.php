<?php

namespace App\Enums;

enum TalentStatus: string
{
    case SYNCED = 'synced';
    case PENDING = 'pending';
    case INACTIVE = 'inactive';
    case ERROR = 'error';
}
