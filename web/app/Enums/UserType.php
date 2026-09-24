<?php

namespace App\Enums;

enum UserType: string
{
    case Owner = 'owner';
    case Staff = 'staff';
    case Customer = 'customer';
}