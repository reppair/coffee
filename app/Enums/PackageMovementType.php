<?php

namespace App\Enums;

enum PackageMovementType: string
{
    case Initial = 'initial';
    case Packaged = 'packaged';
    case Sale = 'sale';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case Adjustment = 'adjustment';
    case Damaged = 'damaged';
}
