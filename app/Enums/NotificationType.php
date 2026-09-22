<?php

namespace App\Enums;

enum NotificationType: string
{
    case ORDER_STATUS = 'order_status';
    case WORKSPACE_UPDATE = 'workspace_update';
    case SYSTEM = 'system';
}
