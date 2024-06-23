<?php

namespace TerminoveTrziste\Models\Database;

use PDO;
use PDOException;

/**
 * PDO databázový wrapper
 * @author Jan Štěch
 */
class Db
{

    private static PDO $connection;
    private static array $settings = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_EMULATE_PREPARES => false
    );

    private const DB_HOST = 'localhost';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_NAME = 'exam-marketplace';

    public static function connect(string $host = self::DB_HOST, string $username = self::DB_USER, string $password = self::DB_PASS, string $database = self::DB_NAME): ?PDO
    {
        try {
            self::$connection = new PDO('mysql:host=' . $host . ';dbname=' . $database, $username, $password,
                self::$settings);
        } catch (PDOException $e) {
            return null;
        }
        return self::$connection;
    }

    /**
     * @throws DatabaseException
     */
    public static function executeQuery(string $query, array $parameters = array(), bool $returnLastId = false)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }
        try {
            $statement = self::$connection->prepare($query);
            $result = $statement->execute($parameters);

            if ($returnLastId) {
                return self::$connection->lastInsertId();
            }
        } catch (PDOException $e) {
            throw new DatabaseException('Database query wasn\'t executed successfully.', null, $e, $query,
                $e->getCode(), $e->errorInfo[2]);
        }
        return $result;
    }

    /**
     * @throws DatabaseException
     */
    public static function fetchQuery(string $query, array $parameters = array(), bool $all = false)
    {
        if (!isset(self::$connection)) {
            self::connect();
        }
        try {
            $statement = self::$connection->prepare($query);
            $statement->execute($parameters);
        } catch (PDOException $e) {
            throw new DatabaseException('Database query wasn\'t executed successfully.', null, $e, $query,
                $e->getCode(), $e->errorInfo[2]);
        }

        if ($statement->rowCount() === 0) {
            return false;
        }
        if ($all) {
            return $statement->fetchAll();
        } else {
            return $statement->fetch();
        }
    }
}

