<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Available = "available";

    case NotAvailable = "not available";

    case Draft = "draft";
}
