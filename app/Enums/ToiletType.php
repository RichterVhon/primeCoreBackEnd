<?php

namespace App\Enums;

enum ToiletType: string
{
    case NONE = 'none';
    case SHARED = 'shared';         // shared among tenants or on the floor
    case EXCLUSIVE = 'exclusive';   // inside the unit
    case OUTSIDE_UNIT = 'outside_unit'; // available outside, e.g., hallway
}
