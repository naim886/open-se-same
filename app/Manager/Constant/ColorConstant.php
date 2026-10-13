<?php

namespace App\Manager\Constant;

use App\Models\Expense;

class ColorConstant
{
    public const EXPENSE_STATUS_COLOR = [
        Expense::STATUS_APPROVED => '#32e600',
        Expense::STATUS_PENDING  => '#ffff00',
        Expense::STATUS_REJECTED => '#FF0000',
    ];
}
