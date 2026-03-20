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

namespace WebtechSolutions\AiCore\Ui\DataProvider\AiLog;

use Magento\Ui\DataProvider\AbstractDataProvider;
use WebtechSolutions\AiCore\Model\ResourceModel\AiLog\Collection;
use WebtechSolutions\AiCore\Model\ResourceModel\AiLog\CollectionFactory;

class Grid extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        $collection = $this->getCollection();
        $collection->setOrder('log_id', 'DESC');

        return [
            'totalRecords' => $collection->getSize(),
            'items'        => array_values($collection->toArray()['items']),
        ];
    }
}
