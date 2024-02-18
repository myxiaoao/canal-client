<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: EntryProtocol.proto

namespace Com\Alibaba\Otter\Canal\Protocol;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 **message row 每行变更数据的数据结构*
 *
 * Generated from protobuf message <code>com.alibaba.otter.canal.protocol.RowChange</code>
 */
class RowChange extends \Google\Protobuf\Internal\Message
{
    /**
     **tableId,由数据库产生*
     *
     * Generated from protobuf field <code>int64 tableId = 1;</code>
     */
    protected $tableId = 0;
    /**
     ** ddl/query的sql语句  *
     *
     * Generated from protobuf field <code>string sql = 11;</code>
     */
    protected $sql = '';
    /**
     ** 一次数据库变更可能存在多行  *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.RowData rowDatas = 12;</code>
     */
    private $rowDatas;
    /**
     **预留扩展*
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Pair props = 13;</code>
     */
    private $props;
    /**
     ** ddl/query的schemaName，会存在跨库ddl，需要保留执行ddl的当前schemaName  *
     *
     * Generated from protobuf field <code>string ddlSchemaName = 14;</code>
     */
    protected $ddlSchemaName = '';
    protected $eventType_present;
    protected $isDdl_present;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $tableId
     *          *tableId,由数据库产生*
     *     @type int $eventType
     *     @type bool $isDdl
     *     @type string $sql
     *          * ddl/query的sql语句  *
     *     @type array<\Com\Alibaba\Otter\Canal\Protocol\RowData>|\Google\Protobuf\Internal\RepeatedField $rowDatas
     *          * 一次数据库变更可能存在多行  *
     *     @type array<\Com\Alibaba\Otter\Canal\Protocol\Pair>|\Google\Protobuf\Internal\RepeatedField $props
     *          *预留扩展*
     *     @type string $ddlSchemaName
     *          * ddl/query的schemaName，会存在跨库ddl，需要保留执行ddl的当前schemaName  *
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\EntryProtocol::initOnce();
        parent::__construct($data);
    }

    /**
     **tableId,由数据库产生*
     *
     * Generated from protobuf field <code>int64 tableId = 1;</code>
     * @return int|string
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     **tableId,由数据库产生*
     *
     * Generated from protobuf field <code>int64 tableId = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTableId($var)
    {
        GPBUtil::checkInt64($var);
        $this->tableId = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.com.alibaba.otter.canal.protocol.EventType eventType = 2;</code>
     * @return int
     */
    public function getEventType()
    {
        return $this->readOneof(2);
    }

    public function hasEventType()
    {
        return $this->hasOneof(2);
    }

    /**
     * Generated from protobuf field <code>.com.alibaba.otter.canal.protocol.EventType eventType = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setEventType($var)
    {
        GPBUtil::checkEnum($var, \Com\Alibaba\Otter\Canal\Protocol\EventType::class);
        $this->writeOneof(2, $var);

        return $this;
    }

    /**
     * Generated from protobuf field <code>bool isDdl = 10;</code>
     * @return bool
     */
    public function getIsDdl()
    {
        return $this->readOneof(10);
    }

    public function hasIsDdl()
    {
        return $this->hasOneof(10);
    }

    /**
     * Generated from protobuf field <code>bool isDdl = 10;</code>
     * @param bool $var
     * @return $this
     */
    public function setIsDdl($var)
    {
        GPBUtil::checkBool($var);
        $this->writeOneof(10, $var);

        return $this;
    }

    /**
     ** ddl/query的sql语句  *
     *
     * Generated from protobuf field <code>string sql = 11;</code>
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     ** ddl/query的sql语句  *
     *
     * Generated from protobuf field <code>string sql = 11;</code>
     * @param string $var
     * @return $this
     */
    public function setSql($var)
    {
        GPBUtil::checkString($var, True);
        $this->sql = $var;

        return $this;
    }

    /**
     ** 一次数据库变更可能存在多行  *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.RowData rowDatas = 12;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getRowDatas()
    {
        return $this->rowDatas;
    }

    /**
     ** 一次数据库变更可能存在多行  *
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.RowData rowDatas = 12;</code>
     * @param array<\Com\Alibaba\Otter\Canal\Protocol\RowData>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setRowDatas($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Com\Alibaba\Otter\Canal\Protocol\RowData::class);
        $this->rowDatas = $arr;

        return $this;
    }

    /**
     **预留扩展*
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Pair props = 13;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     **预留扩展*
     *
     * Generated from protobuf field <code>repeated .com.alibaba.otter.canal.protocol.Pair props = 13;</code>
     * @param array<\Com\Alibaba\Otter\Canal\Protocol\Pair>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setProps($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Com\Alibaba\Otter\Canal\Protocol\Pair::class);
        $this->props = $arr;

        return $this;
    }

    /**
     ** ddl/query的schemaName，会存在跨库ddl，需要保留执行ddl的当前schemaName  *
     *
     * Generated from protobuf field <code>string ddlSchemaName = 14;</code>
     * @return string
     */
    public function getDdlSchemaName()
    {
        return $this->ddlSchemaName;
    }

    /**
     ** ddl/query的schemaName，会存在跨库ddl，需要保留执行ddl的当前schemaName  *
     *
     * Generated from protobuf field <code>string ddlSchemaName = 14;</code>
     * @param string $var
     * @return $this
     */
    public function setDdlSchemaName($var)
    {
        GPBUtil::checkString($var, True);
        $this->ddlSchemaName = $var;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventTypePresent()
    {
        return $this->whichOneof("eventType_present");
    }

    /**
     * @return string
     */
    public function getIsDdlPresent()
    {
        return $this->whichOneof("isDdl_present");
    }

}
