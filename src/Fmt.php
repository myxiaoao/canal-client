<?php

namespace Cooper\CanalClient;

use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use Exception;

class Fmt
{
    /**
     * @param Entry $entry
     * @throws Exception
     */
    public static function println(Entry $entry): void
    {
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                return;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $evenType = $rowChange->getEventType();
        $header = $entry->getHeader();
        if ($header === null) {
            return;
        }

        echo sprintf(
            "# server %s, binlog[%s: %d], name[%s, %s], eventType: %s",
            $header->getServerId(),
            $header->getLogfileName(),
            $header->getLogfileOffset(),
            $header->getSchemaName(),
            $header->getTableName(),
            $header->getEventType()
        ), PHP_EOL, PHP_EOL;

        $sql = $rowChange->getSql();
        $sql = (! empty($sql)) ? sprintf('%s;', $sql) : '';
        echo $sql, PHP_EOL;

        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            switch ($evenType) {
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
        foreach ($columns as $column) {
            echo sprintf(
                "%s: %s, update= %s",
                $column->getName(),
                $column->getValue(),
                var_export($column->getUpdated(), true)
            ), PHP_EOL;
        }
    }
}