<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Available = "available";

    case Unvailable = "unavailable";

    case Draft = "draft";
}
