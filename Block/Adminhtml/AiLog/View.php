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

namespace WebtechSolutions\AiCore\Block\Adminhtml\AiLog;

use Magento\Backend\Block\Template;
use WebtechSolutions\AiCore\Model\AiLog;

class View extends Template
{
    protected $_template = 'WebtechSolutions_AiCore::ailog/view.phtml';

    public function getLog(): ?AiLog
    {
        return $this->getData('log');
    }

    public function getStatusStyle(string $status): string
    {
        return match ($status) {
            'success'      => 'background:#388e3c;color:#fff',
            'error'        => 'background:#d32f2f;color:#fff',
            'rate_limited' => 'background:#f57c00;color:#fff',
            'auth_error'   => 'background:#7b1fa2;color:#fff',
            default        => 'background:#757575;color:#fff',
        };
    }

    public function formatJson(?string $json): string
    {
        if ($json === null || $json === '') {
            return '—';
        }
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $json;
        }
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
