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

/**
 * Truncates long text columns in the grid to keep the table readable.
 */
class Truncated extends Column
{
    private const MAX_LENGTH = 120;

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            $value = $item[$fieldName] ?? '';
            if (mb_strlen((string)$value) > self::MAX_LENGTH) {
                $item[$fieldName] = mb_substr((string)$value, 0, self::MAX_LENGTH) . '…';
            }
        }

        return $dataSource;
    }
}
