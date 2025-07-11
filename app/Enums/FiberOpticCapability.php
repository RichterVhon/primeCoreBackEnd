<?php

namespace App\Enums;

enum FiberOpticCapability: string
{
    case NOT_AVAILABLE = 'not_available';
    case AVAILABLE = 'available';
    case MULTIPLE_PROVIDERS = 'multiple_providers';
}
