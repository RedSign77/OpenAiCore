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

namespace WebtechSolutions\AiCore\Controller\Adminhtml\AiLog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use WebtechSolutions\AiCore\Model\AiLogFactory;
use WebtechSolutions\AiCore\Model\ResourceModel\AiLog as AiLogResource;

class ViewModal extends Action
{
    public const ADMIN_RESOURCE = 'WebtechSolutions_AiCore::ai_logs';

    public function __construct(
        Context $context,
        private readonly RawFactory    $resultRawFactory,
        private readonly LayoutFactory $layoutFactory,
        private readonly AiLogFactory  $aiLogFactory,
        private readonly AiLogResource $aiLogResource
    ) {
        parent::__construct($context);
    }

    public function execute(): \Magento\Framework\Controller\Result\Raw
    {
        $id     = (int)$this->getRequest()->getParam('id');
        $result = $this->resultRawFactory->create();

        $log = $this->aiLogFactory->create();
        $this->aiLogResource->load($log, $id);

        if (!$log->getId()) {
            $result->setContents('<p style="padding:20px;color:#d32f2f">' . __('Record not found.') . '</p>');
            return $result;
        }

        $layout = $this->layoutFactory->create();
        $block  = $layout->createBlock(
            \WebtechSolutions\AiCore\Block\Adminhtml\AiLog\View::class,
            'aicore.ailog.view',
            ['data' => ['log' => $log]]
        );
        $block->setTemplate('WebtechSolutions_AiCore::ailog/view.phtml');

        $result->setContents($block->toHtml());
        return $result;
    }
}
