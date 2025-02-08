<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use Exception;
use mysqli;
use mysqli_result;

class DB
{
    private Configuration $configuration;
    private mysqli|null $mysqllink;

    public function __construct(
        Configuration $configuration
    ) {
        $this->configuration = $configuration;
        $this->mysqllink = null;
    }

    /*
     * Connect to the database.
     */
    public function connect(): void
    {
        $mysqllink = false;

        while (!$mysqllink) {
            $mysqllink = mysqli_connect(
                $this->configuration->getDBHost(),
                $this->configuration->getDBUser(),
                $this->configuration->getDBPass(),
                $this->configuration->getDBName()
            );
        }

        $this->mysqllink = $mysqllink;
    }

    public function getMySQLLink(): mysqli|bool
    {
        if ($this->mysqllink != null) {
            return $this->mysqllink;
        }
        return false;
    }

    /*
     * Send a mysql query to the database and return the result.
     */
    public function query(string $query): mysqli_result | bool
    {
        if ($this->mysqllink == null) {
            throw new Exception();
        }
        return mysqli_query($this->mysqllink, $query);
    }

    /*
     * Send a mysql query to the database and return whether there is an existing row for this query.
     */
    public function isExisting(string $query): bool
    {
        if ($this->mysqllink == null) {
            throw new Exception();
        }
        $res = mysqli_query($this->mysqllink, $query);

        if (is_bool($res)) {
            throw new Exception();
        }

        $numRows = mysqli_num_rows($res);

        return ($numRows > 0);
    }

    /**
    * Returns an array out of a mysql result.
    * @return array<mixed>|false|null
    */
    public function fetchArray(mysqli_result|bool $result): array|false|null
    {
        if (is_bool($result)) {
            throw new Exception();
        }

        return mysqli_fetch_array($result);
    }

    /*
    * Escapes a given string and returns the clean result.
    */
    public function escapeString(string $dirt): string
    {
        if ($this->mysqllink == null) {
            throw new Exception();
        }

        return mysqli_real_escape_string($this->mysqllink, $dirt);
    }

    /*
    * Returns the auto generated id used in the latest query.
    */
    public function lastInsertedID(): string|int
    {
        if ($this->mysqllink == null) {
            throw new Exception();
        }

        return mysqli_insert_id($this->mysqllink);
    }

    /*
    * Returns the number of rows in the result
    */
    public function getRowCount(mysqli_result|bool $queryResult): int
    {

        if (is_bool($queryResult)) {
            throw new Exception();
        }

        $row = $this->fetchArray($queryResult);

        if (is_null($row) || !$row) {
            throw new Exception();
        }

        $result = 0;

        if (is_string($row['rowcount'])) {
            $result = intval(strval($row['rowcount']));
        }

        return $result;
    }

    /*
     * Close the connection to the database.
     */
    public function close(): void
    {
        if ($this->mysqllink != null) {
            mysqli_close($this->mysqllink);
        }
    }

    public function isHealthy(): bool
    {
        $result = true;
        try {
            $this->connect();
        } catch (Exception $e) {
            $result = false;
        }

        $result = $result && $this->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('root') LIMIT 1");

        try {
            $this->close();
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }
}
