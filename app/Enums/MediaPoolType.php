<?php

namespace App\Enums;

enum MediaPoolType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case LINK  = 'link';
}
