<?php

/**
 * MageSentinel / MageInsight Pro
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Proprietary License that is bundled
 * with this package in the file COPYING.txt.
 * It is also available through the world-wide-web at this URL:
 * [A weboldalad URL-je]/license
 *
 * @category   Vendor
 * @package    WebtechSolutions_AiCore
 * @copyright  Copyright (c) 2026 Webtech Solutions
 * @license    Proprietary
 */

declare(strict_types=1);

namespace WebtechSolutions\AiCore\Block\Adminhtml\Config;

class SyncModelsButton extends AbstractSyncModelsButton
{
    public function getSyncUrl(): string
    {
        return $this->getUrl('aicore/models/sync');
    }
}
