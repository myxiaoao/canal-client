<?php

namespace Cooper\CanalClient;

interface ICanalConnector
{
    public function connect();

    public function disConnect();

    public function checkValid();

    public function subscribe();

    public function unSubscribe();

    public function get();

    public function getWithoutAck();

    public function ack();

    public function rollback();
}