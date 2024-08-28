<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace ui;

use DateTimeImmutable;
use Generator;
use ilCtrl;
use ilCtrlException;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ilObjDHBWTrainingGUI;

/**
 * Class DHBWExportsTable
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWExportsTable implements DataRetrieval
{
    private Factory $factory;
    private ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->ctrl = $DIC->ctrl();
    }

    public function getRows(DataRowBuilder $row_builder, array $visible_column_ids, Range $range, Order $order, ?array $filter_data, ?array $additional_parameters): Generator
    {
        foreach ($this->doSelect($order, $range) as $index => $record) {
            yield $row_builder->buildDataRow((string) $index, $record);
        }
    }

    protected function doSelect(Order $order, Range $range): array
    {
        $sql_order_part = $order->join('ORDER BY', fn (...$o) => implode(' ', $o));
        $sql_range_part = sprintf('LIMIT %2$s OFFSET %1$s', ...$range->unpack());
        return array_map(
            fn ($rec) => array_merge($rec, ['sql_order' => $sql_order_part, 'sql_range' => $sql_range_part]),
            $this->getRecords()
        );
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getRecords());
    }

    /**
     * @throws ilCtrlException
     */
    private function getRecords(): array
    {
        $actions = [
            [
                'file' => 'Esto es un registro falso para probar la tabla.zip',
                'size' => '1.2 MB',
                'date' => new DateTimeImmutable('2021-01-01 12:00:00')
            ]
        ];

        foreach ($actions as $index => $action) {
            $this->ctrl->setParameterByClass(ilObjDHBWTrainingGUI::class, 'file', $action['file']);

            $actions[$index]['actions'] = $this->factory->link()->standard(
                'Download',
                $this->ctrl->getLinkTargetByClass(ilObjDHBWTrainingGUI::class, 'downloadExport')
            );
        }

        return $actions;
    }
}