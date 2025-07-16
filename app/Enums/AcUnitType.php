<?php

namespace App\Enums;

enum AcUnitType: string
{
    case NONE = 'none';
    case WINDOW_TYPE = 'window_type';
    case SPLIT_TYPE = 'split-type';
    case CENTRALIZED = 'centralized';
    case VRF_VRV = 'vrf_vrv'; // Variable Refrigerant Flow / Volume
}
