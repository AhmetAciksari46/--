<?php

namespace App\Enums;

enum GroupType: string
{
    // Global
    case GlobalGeneral   = 'global_general';
    case GlobalManager   = 'global_manager';
    case GlobalYonetim   = 'global_yonetim';

        // School based
    case SchoolGeneral   = 'school_general';
    case SchoolManagement = 'school_management';

        // Classroom
    case Classroom       = 'classroom';
}
