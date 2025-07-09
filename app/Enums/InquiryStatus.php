<?php

namespace App\Enums;

enum InquiryStatus: string
{
    case Pending = "pending";

    case Deleted = "deleted";

    case responded = "responded";

    case Archived = "archived";

}
