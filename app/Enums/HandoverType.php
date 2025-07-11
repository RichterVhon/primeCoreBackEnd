<?php

namespace App\Enums;

enum HandoverType: string
{
    case BARE = 'bare'; // No flooring, no ceiling, no partition
    case WARM_SHELL = 'warm_shell'; // Basic finishes: ceiling, lighting, air conditioning
    case FITTED = 'fitted'; // Fully fitted with partitions, workstations, etc.
    case FURNISHED = 'furnished'; // Includes furniture and interior
    case AS_IS_WHERE_IS = 'as_is_where_is'; // Delivered as currently existing
}
