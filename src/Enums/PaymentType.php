<?php

declare(strict_types=1);

namespace RetailCosmos\IoiCityMallSalesFile\Enums;

use BenSampo\Enum\Enum;

final class PaymentType extends Enum
{
    const CASH = 'cash';

    const TNG = 'tng';

    const VISA = 'visa';

    const MASTERCARD = 'mastercard';

    const AMEX = 'amex';

    const VOUCHER = 'voucher';

    const OTHERS = 'others';
}
