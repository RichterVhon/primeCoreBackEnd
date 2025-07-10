<?php

namespace App\Enums;

enum LotCondition: string
{
    case WithExistingStructure = 'with existing structure';
    case WithFoliage = 'with foliage';
    case Vacant = 'vacant';

}
