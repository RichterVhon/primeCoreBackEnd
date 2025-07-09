<?php

namespace App\Enums;

enum AuthorityType: string
{
    case Soft = 'Soft';
    case Open = 'Open';
    case NonExclusive = 'Non-Exclusive';
    case LeadBroker = 'Lead Broker';
    case SoleAgency = 'Sole Agency';
    case Exclusive = 'Exclusive';
}
