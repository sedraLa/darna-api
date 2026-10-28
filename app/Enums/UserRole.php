<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin    = 'admin';
    case Office   = 'office';
    case Customer = 'customer';
}
