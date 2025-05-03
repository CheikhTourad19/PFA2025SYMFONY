<?php

namespace App\Enum;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::COMPLETED => 'Terminée'
        };
    }
}
