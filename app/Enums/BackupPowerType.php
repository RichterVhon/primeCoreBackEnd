<?php

namespace App\Enums;

enum BackupPowerType: string
{
    case NONE = 'none';
    case PARTIAL = 'partial';   // for common areas only
    case FULL = 'full';         // entire building including tenant areas
}
