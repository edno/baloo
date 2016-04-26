<?php

namespace Baloo;

abstract class BalooDataClass
{

    protected static $pdo;

    private $id;

    public function __construct(...$params)
    {
    }

    public function save()
    {
    }

    public function delete()
    {
    }

    public function getId()
    {
    }

    private function __getRecordInstanceByColumn(string $column, string $value, string $join = null)
    {
    }
}
