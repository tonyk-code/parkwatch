<?php

namespace App\Enums;

enum SpotStatus: string
{
    case Free = 'free';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case Offline = 'offline';
}