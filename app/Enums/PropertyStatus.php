<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Pending   = 'pending';
    case Available = 'available';
    case Reserved  = 'reserved';
    case Sold      = 'sold';
    case Rejected  = 'rejected';
}
