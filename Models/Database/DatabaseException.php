<?php

namespace TerminoveTrziste\Models\Database;

class DatabaseException extends \Exception
{

    /**
     * @param string $string
     * @param null $null
     * @param \Exception|PDOException $e
     * @param string $query
     * @param int|mixed $getCode
     * @param mixed $int
     */
    public function __construct(string $string, $null, $e, string $query, $getCode, $int)
    {
    }
}