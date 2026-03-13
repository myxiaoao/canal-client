<?php

namespace Cooper\CanalClient;

use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;

class Fmt
{
    /**
     * @param Entry $entry
     * @return void
     */
    public static function println(Entry $entry): void
    {
        echo $entry->getEntryType(), PHP_EOL;

        $entryType = $entry->getEntryType();
        if ($entryType === EntryType::TRANSACTIONBEGIN || $entryType === EntryType::TRANSACTIONEND) {
            return;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $eventType = $rowChange->getEventType();
        $header = $entry->getHeader();
        if ($header === null) {
            return;
        }

        printf(
            "# server %s, binlog[%s: %d], name[%s, %s], eventType: %s%s%s",
            $header->getServerId(),
            $header->getLogfileName(),
            $header->getLogfileOffset(),
            $header->getSchemaName(),
            $header->getTableName(),
            $header->getEventType(),
            PHP_EOL,
            PHP_EOL
        );

        $sql = $rowChange->getSql();
        echo !empty($sql) ? "{$sql};" : 'row 模式，针对 DML 默认没有 SQL 语句';
        echo PHP_EOL, PHP_EOL;

        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            switch ($eventType) {
                case EventType::DELETE:
                    self::ptColumn($rowData->getBeforeColumns());
                    break;
                case EventType::INSERT:
                    self::ptColumn($rowData->getAfterColumns());
                    break;
                default:
                    echo '> before data', PHP_EOL;
                    self::ptColumn($rowData->getBeforeColumns());
                    echo PHP_EOL;
                    echo '> after data', PHP_EOL;
                    self::ptColumn($rowData->getAfterColumns());
                    break;
            }
        }
    }

    /**
     * @param mixed $columns
     * @return void
     */
    private static function ptColumn(mixed $columns): void
    {
        foreach ($columns as $key => $column) {
            printf(
                "#%d %s: %s, update=%s%s",
                $key + 1,
                $column->getName(),
                $column->getValue(),
                var_export($column->getUpdated(), true),
                PHP_EOL
            );
        }
    }
}
