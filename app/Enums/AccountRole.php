<?php

namespace App\Enums;

enum AccountRole: string
{
    case Admin = 'admin';
    case Agent = 'user';
    case Viewer = 'viewer';
}
