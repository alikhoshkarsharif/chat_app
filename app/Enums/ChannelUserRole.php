<?php

namespace App\Enums;

enum ChannelUserRole: int
{
    case MEMBER = 1;
    case ADMIN = 2;
    case OWNER = 3;
}
