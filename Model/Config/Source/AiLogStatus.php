<?php

/**
 * AiCore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Proprietary License that is bundled
 * with this package in the file COPYING.txt.
 *
 * @category   Vendor
 * @package    WebtechSolutions_AiCore
 * @copyright  Copyright (c) 2026 Webtech Solutions
 * @license    Proprietary
 */

declare(strict_types=1);

namespace WebtechSolutions\AiCore\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AiLogStatus implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'success',      'label' => __('Success')],
            ['value' => 'error',        'label' => __('Error')],
            ['value' => 'rate_limited', 'label' => __('Rate Limited')],
            ['value' => 'auth_error',   'label' => __('Auth Error')],
        ];
    }
}
