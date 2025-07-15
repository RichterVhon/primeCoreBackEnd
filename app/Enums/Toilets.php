<?php

namespace App\Enums;

enum Toilets: string
{
    case WITH_EXISTING = 'with existing';
    case WITH_PROVISION = 'with provision';
    case COMMON_TOILET = 'common toilet';
}
