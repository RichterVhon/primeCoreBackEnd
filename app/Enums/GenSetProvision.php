<?php

namespace App\Enums;

enum GenSetProvision: string
{
    case WITH_PROVISION = 'with provison';
    case WITHOUT_PROVISION = 'without provision';
    case WITH_EXISTING = 'with existing';
    case NOT_APPLICABLE = 'not applicable';
}
