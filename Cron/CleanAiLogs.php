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

namespace WebtechSolutions\AiCore\Cron;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class CleanAiLogs
{
    private const TABLE      = 'webtechsolutions_aicore_log';
    private const RETENTION_DAYS = 7;

    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly LoggerInterface    $logger
    ) {
    }

    public function execute(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $table      = $this->resourceConnection->getTableName(self::TABLE);

        $cutoff = new \DateTime('now', new \DateTimeZone('UTC'));
        $cutoff->modify('-' . self::RETENTION_DAYS . ' days');

        $deleted = $connection->delete(
            $table,
            ['created_at < ?' => $cutoff->format('Y-m-d H:i:s')]
        );

        $this->logger->info(sprintf(
            'AiCore: Cleaned %d AI log record(s) older than %d days.',
            $deleted,
            self::RETENTION_DAYS
        ));
    }
}
