<?php

namespace App\Enums;

enum ElectricalLoadCapacity: string
{
    case SinglePhase = 'single phase';
    case ThreePhase = 'three phase';
}
