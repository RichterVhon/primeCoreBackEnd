<?php

namespace App\Enums;

enum AccountRole: string
{
    case Admin = 'admin';
    case Agent = 'agent';
    case Viewer = 'viewer';
}
