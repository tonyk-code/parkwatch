<?php

namespace App\Enums;

enum SessionMode: string
{
    case Gate = 'gate';
    case Spot = 'spot';
}