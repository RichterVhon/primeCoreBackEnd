<?php

namespace App\Enums;

enum ZoningClassification: string
{
    case Residential = 'residential';
    case Commercial = 'commercial';
    case Industrial = 'industrial';
    case Agricultural = 'agricultural';
}
