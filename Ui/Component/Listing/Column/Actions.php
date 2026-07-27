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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Reusable "View" row-action column. The view route and the entity id
 * field name are configurable via UI Component XML <argument> nodes so
 * this single class can serve every grid that needs a view-modal link.
 */
class Actions extends Column
{
    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        private readonly string $viewRoute = 'aicore/aiLog/viewModal',
        private readonly string $idField = 'log_id'
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')] = [
                'view' => [
                    'href'     => $this->urlBuilder->getUrl(
                        $this->viewRoute,
                        ['id' => $item[$this->idField]]
                    ),
                    'label'    => __('View'),
                    'callback' => [
                        'provider' => '${ $.parentName }',
                        'target'   => 'viewRecord',
                        'params'   => ['id' => $item[$this->idField]],
                    ],
                ],
            ];
        }

        return $dataSource;
    }
}
