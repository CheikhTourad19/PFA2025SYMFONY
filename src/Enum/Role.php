<?php

namespace App\Enum;

enum Role: string
{
    case ADMIN = 'admin';
    case MEDECIN = 'medecin';
    case PATIENT = 'patient';
    case PHARMACIE = 'pharmacie';
    case INFERMIER = 'infermier';
}
