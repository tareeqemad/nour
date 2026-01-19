<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case EnergyAuthority = 'energy_authority';
    case CompanyOwner = 'company_owner';
    case Employee = 'employee';
    case Technician = 'technician';
    case CivilDefense = 'civil_defense';
}
