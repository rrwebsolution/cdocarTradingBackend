<?php

namespace App\Support;

final class PaymentMethod
{
    public const ALL = ['cash', 'gcash', 'bpi', 'bdo'];

    /**
     * Payment methods backed by a static receiving QR code image.
     */
    public const QR_BACKED = ['gcash', 'bpi', 'bdo'];
}
