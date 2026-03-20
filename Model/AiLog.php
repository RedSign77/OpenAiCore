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

namespace WebtechSolutions\AiCore\Model;

use Magento\Framework\Model\AbstractModel;
use WebtechSolutions\AiCore\Model\ResourceModel\AiLog as AiLogResource;

class AiLog extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(AiLogResource::class);
    }
}
