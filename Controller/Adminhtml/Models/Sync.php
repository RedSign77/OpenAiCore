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

namespace WebtechSolutions\AiCore\Controller\Adminhtml\Models;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use WebtechSolutions\AiCore\Model\OpenAi\ModelSync;

class Sync extends Action
{
    public const ADMIN_RESOURCE = 'WebtechSolutions_AiCore::config';

    public function __construct(
        Context $context,
        private readonly ModelSync $modelSync
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $result = $this->modelSync->sync();

        if ($result !== null) {
            $this->messageManager->addSuccessMessage(
                (string)__('AI models synced successfully. %1 models available.', count($result))
            );
        } else {
            $this->messageManager->addErrorMessage(
                (string)__('Failed to sync AI models. Check the API key and logs.')
            );
        }

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('adminhtml/system_config/edit', ['section' => 'aicore']);

        return $redirect;
    }
}
