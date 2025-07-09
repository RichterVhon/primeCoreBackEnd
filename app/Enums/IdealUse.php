<?php

namespace App\Enums;

enum IdealUse: string
{
    case Storage = 'Storage';
    case Manufacturing = 'Manufacturing';
    case DistributionCenter = 'Distribution Center';
    case Combined = 'Storage/Manufacturing/Distribution Center';
}
