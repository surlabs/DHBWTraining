<?php
declare(strict_types=1);
/**
 * License disclaimer
 */

namespace ui;

use DateTimeImmutable;
use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

/**
 * Class DHBWParticipantsTable
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class DHBWParticipantsTable implements DataRetrieval
{
    private array $records = [];

    public function getRows(DataRowBuilder $row_builder, array $visible_column_ids, Range $range, Order $order, ?array $filter_data, ?array $additional_parameters): Generator
    {
        foreach ($this->doSelect($order, $range) as $record) {
            $row_id = $record['username'];

            yield $row_builder->buildDataRow((string) $row_id, $record);
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

    private function getRecords(): array
    {
        return $this->records;
    }

    public function setRecords(array $records): void
    {
        $this->records = $records;
    }
}