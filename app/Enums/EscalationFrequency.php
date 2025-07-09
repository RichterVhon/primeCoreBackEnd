<?php

namespace App\Enums;

enum EscalationFrequency: string
{
    case Monthly = "monthly";
    case Quarterly = "quarterly";
    case Anually = "anually";
}
