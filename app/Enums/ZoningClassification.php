<?php

namespace App\Enums;

enum ZoningClassification:string
{
    case Commercial = 'commercial';  
    case Residential = 'residential';
    case Industrial = 'industrial';
    case Agricultural = 'agricultural';

}
