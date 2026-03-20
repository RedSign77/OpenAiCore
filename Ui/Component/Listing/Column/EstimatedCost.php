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

namespace WebtechSolutions\AiCore\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class EstimatedCost extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $cost = $item[$this->getData('name')] ?? null;
            if ($cost !== null) {
                $formatted = '$' . number_format((float)$cost, 6);
                $item[$this->getData('name')] = '<span style="font-family:monospace;color:#555">'
                    . htmlspecialchars($formatted) . '</span>';
            } else {
                $item[$this->getData('name')] = '<span style="color:#aaa">—</span>';
            }
        }

        return $dataSource;
    }
}
