<?php

namespace App\Enums;

enum TypeOfLoadingBay: string
{
    case FlushDock = 'Flush Dock';
    case OpenDock = 'Open Dock';
    case SawToothDock = 'Saw Tooth Dock';
    case EnclosedDock = 'Enclosed Dock';
    case DepressedDock = 'Depressed Dock';
    case FingerDock = 'Finger Dock';

}
