<?php

namespace App\Enums;

enum MessageType: string
{
    case Normal       = 'normal';
    case Announcement = 'announcement';
    case Homework     = 'homework';
    case File         = 'file';
    case Image        = 'image';
    case Video        = 'video';
}
