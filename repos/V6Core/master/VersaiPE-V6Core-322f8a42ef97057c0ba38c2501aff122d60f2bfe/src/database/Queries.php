<?php

namespace Versai\V6\database;

interface Queries{

    public const PREFIX = "versai.";
    public const TABLES = self::PREFIX . "init.";
    public const INSERT = self::PREFIX . "insert.";
    public const SELECT = self::PREFIX . "select.";


}