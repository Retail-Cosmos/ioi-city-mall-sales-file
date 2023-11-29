<?php

namespace RetailCosmos\IoiCityMallSalesFile\Enums;

enum PaymentType: string
{
    case CASH = 'cash';

    case TNG = 'tng';

    case VISA = 'visa';

    case MASTERCARD = 'mastercard';

    case AMEX = 'amex';

    case VOUCHER = 'voucher';

    case OTHERS = 'others';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
