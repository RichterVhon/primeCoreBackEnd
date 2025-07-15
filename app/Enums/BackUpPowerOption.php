<?php

namespace App\Enums;

enum BackUpPowerOption: string
{
    case FULL_BACK_UP = 'full back up';
    case PARTIAL_BACK_UP ='partial back up';
    case NO_BACK_UP = 'no back up';
    case NOT_APPLICABLE  ='not applicable';
}
