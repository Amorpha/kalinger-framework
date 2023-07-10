<?php

namespace Kalinger;

use PDO;

class DBM {

    protected $db;

    /**
     * Object PDO.
     */
    public $dbh = null;

    /**
     * Statement Handle.
     */
    public $sth = null;

    /**
     * Executed SQL query.
     */
    public $query = '';

    /**
     * DB connection
     */

    public $dbname, $dbhost, $dbuser, $dbpassword, $dbcharset;

    public function __construct($dbname, $dbhost, $dbuser, $dbpassword, $dbcharset = 'utf8') {

        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbcharset = $dbcharset;

    }

    public function getDbh() {

        if (!$this->dbh) {

            try {

                $this->dbh = new PDO (
                    'mysql:dbname=' . $this->dbname . ';host=' . $this->dbhost . ';',
                    $this->dbuser,
                    $this->dbpassword,
                    [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->dbcharset]
                );

                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

            } catch (PDOException $e) {

                exit('Error connecting to database: ' . $e->getMessage());

            }

        }

        return $this->dbh;

    }

    /**
     * Add to table, if successful, will return the inserted ID, otherwise 0.
     */
    public function add($query, $param = []) {

        $this->sth = $this->getDbh()->prepare($query);

        return ($this->sth->execute((array)$param)) ? $this->getDbh()->lastInsertId() : 0;

    }

    /**
     * Query execution.
     */
    public function set($query, $param = []) {

        $this->sth = $this->getDbh()->prepare($query);

        return $this->sth->execute((array)$param);

    }

    /**
     * Getting a row from a table.
     */
    public function getRow($query, $param = []) {

        $this->sth = $this->getDbh()->prepare($query);

        $this->sth->execute((array)$param);

        return $this->sth->fetch(PDO::FETCH_ASSOC);

    }

    /**
     * Getting all rows from a table.
     */
    public function getAll($query, $param = []) {

        $this->sth = $this->getDbh()->prepare($query);

        $this->sth->execute((array)$param);

        return $this->sth->fetchAll(PDO::FETCH_ASSOC);

    }

    /**
     * Getting value.
     */
    public function getValue($query, $param = [], $default = null) {

        $result = $this->getRow($query, $param);

        if (!empty($result)) {

            $result = array_shift($result);

        }

        return (empty($result)) ? $default : $result;

    }

    /**
     * Getting a column from a table.
     */
    public function getColumn($query, $param = []) {

        $this->sth = $this->getDbh()->prepare($query);

        $this->sth->execute((array)$param);

        return $this->sth->fetchAll(PDO::FETCH_COLUMN);

    }
}
