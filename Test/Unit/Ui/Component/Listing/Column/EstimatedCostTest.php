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

namespace WebtechSolutions\AiCore\Test\Unit\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\TestCase;
use WebtechSolutions\AiCore\Ui\Component\Listing\Column\EstimatedCost;

class EstimatedCostTest extends TestCase
{
    private EstimatedCost $column;

    protected function setUp(): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('getProcessor')->willReturn(
            $this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class)
        );
        $uiComponentFactory = $this->createMock(UiComponentFactory::class);

        $this->column = new EstimatedCost(
            $context,
            $uiComponentFactory,
            [],
            ['name' => 'estimated_cost']
        );
    }

    public function testPrepareDataSourceReturnsSameWhenNoItems(): void
    {
        $result = $this->column->prepareDataSource([]);
        $this->assertSame([], $result);
    }

    public function testPrepareDataSourceFormatsCostWithDollarSign(): void
    {
        $dataSource = ['data' => ['items' => [['estimated_cost' => 0.000123]]]];
        $result     = $this->column->prepareDataSource($dataSource);
        $html       = $result['data']['items'][0]['estimated_cost'];

        $this->assertStringContainsString('$0.000123', $html);
    }

    public function testPrepareDataSourceFormatsWithSixDecimalPlaces(): void
    {
        $dataSource = ['data' => ['items' => [['estimated_cost' => 1.5]]]];
        $result     = $this->column->prepareDataSource($dataSource);
        $html       = $result['data']['items'][0]['estimated_cost'];

        $this->assertStringContainsString('$1.500000', $html);
    }

    public function testPrepareDataSourceRendersEmDashWhenCostIsNull(): void
    {
        $dataSource = ['data' => ['items' => [['estimated_cost' => null]]]];
        $result     = $this->column->prepareDataSource($dataSource);
        $html       = $result['data']['items'][0]['estimated_cost'];

        $this->assertStringContainsString('—', $html);
    }

    public function testPrepareDataSourceUsesMonospaceFont(): void
    {
        $dataSource = ['data' => ['items' => [['estimated_cost' => 0.001]]]];
        $result     = $this->column->prepareDataSource($dataSource);
        $html       = $result['data']['items'][0]['estimated_cost'];

        $this->assertStringContainsString('monospace', $html);
    }

    public function testPrepareDataSourceHandlesZeroCost(): void
    {
        $dataSource = ['data' => ['items' => [['estimated_cost' => 0.0]]]];
        $result     = $this->column->prepareDataSource($dataSource);
        $html       = $result['data']['items'][0]['estimated_cost'];

        $this->assertStringContainsString('$0.000000', $html);
    }
}
