<?php

namespace App\Enums;

enum TaxOnCusa: string
{
    case VAT_EXCLUSIVE = 'VAT_Exclsuive';
    case VAT_EXEMPT = 'vat_exempt';    // Exempt from VAT (e.g., small entity, residential CUSA)
    case NON_TAXABLE = 'non_taxable';
}
