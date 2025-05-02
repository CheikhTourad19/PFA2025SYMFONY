<?php

namespace App\Enum;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
}
