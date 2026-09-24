<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Active = 'active';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Completed = 'completed';
}