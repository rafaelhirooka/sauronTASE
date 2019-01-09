<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 03/10/2017
 * Time: 14:00
 */

namespace App\Classes\v1\DB;

include_once("PDO.Log.class.php");

abstract class DBC implements \DBInterface {
    private $Host;
    private $DBName;
    private $DBUser;
    private $DBPassword;
    private $type;

    protected $pdo;
    private $sQuery;
    private $bConnected = false;
    private $log;
    private $parameters;
    public $rowCount   = 0;
    public $columnCount   = 0;
    public $querycount = 0;


    public function __construct($host, $user, $pass, $name, $type = 'sqlsrv') {
        $this->log        = new Log();
        $this->Host       = $host;
        $this->DBName     = $name;
        $this->DBUser     = $user;
        $this->DBPassword = $pass;
        $this->type = $type;

        $this->Connect();
        $this->parameters = array();
    }


    public function Connect() {
        try {
            switch ($this->type) {
                // Connect with SqlServer
                case "sqlsrv":
                    $this->pdo = new \PDO("sqlsrv:server=".$this->Host." ; Database = ". $this->DBName,
                        $this->DBUser, $this->DBPassword);

                    break;

                // Connect with MySql
                case "mysql":
                    $this->pdo = new \PDO('mysql:dbname=' . $this->DBName . ';host=' . $this->Host . ';charset=utf8',
                        $this->DBUser,
                        $this->DBPassword,
                        array(
                            //For PHP 5.3.6 or lower
                            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                            \PDO::ATTR_EMULATE_PREPARES => false,
                            //PDO::ATTR_PERSISTENT => true,

                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                        )
                    );

                    break;
            }


            $this->bConnected = true;

        }
        catch (\PDOException $e) {
            throw new \Exception($this->ExceptionLog($e->getMessage()));
        }
    }


    public function CloseConnection() {
        $this->pdo = null;
    }


    private function Init($query, $parameters = array()) {
        if (!$this->bConnected) {
            $this->Connect();
        }
        try {

            $p_arr = array();
            // Order the array
            if (!empty($parameters)) {
                foreach ($parameters as $v) {
                    $p_arr[] = $v;
                }
            }
            $this->parameters = $p_arr;
            $this->sQuery = $this->pdo->prepare($query);

            if ($this->pdo->prepare($query)) {
                if (!empty($this->parameters)) {
                    $this->succes = $this->sQuery->execute($this->parameters);
                } else {
                    $this->succes = $this->sQuery->execute();
                }

                $this->querycount++;
            } else {
                throw new \Exception(json_encode($this->pdo->errorInfo()));
            }


        }
        catch (\PDOException $e) {

            throw new \Exception($this->ExceptionLog($e->getMessage(), $this->BuildParams($query)));
        }
        $this->parameters = array();
    }

    private function BuildParams($query, $params = null) {
        if (!empty($params)) {
            $rawStatement = explode(" ", $query);
            foreach ($rawStatement as $value) {
                if (strtolower($value) == 'in') {
                    return str_replace("(?)", "(" . implode(",", array_fill(0, count($params), "?")) . ")", $query);
                }
            }
        }
        return $query;
    }


    protected function query($query, $params = null, $fetchmode = \PDO::FETCH_ASSOC) {
        $query        = trim($query);
        $rawStatement = explode(" ", $query);
        $this->Init($query, $params);
        $statement = strtolower($rawStatement[0]);

        if ($this->succes) {
            if ($statement === 'select' || $statement === 'show') {
                return $this->sQuery->fetchAll($fetchmode);
            } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
                return $this->sQuery->rowCount();
            } else {
                return NULL;
            }
        } else {
            throw new \Exception(json_encode($this->pdo->errorInfo()));
        }


    }



    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }


    public function column($query, $params = null) {
        $this->Init($query, $params);
        $resultColumn = $this->sQuery->fetchAll(PDO::FETCH_COLUMN);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();
        return $resultColumn;
    }

    public function row($query, $params = null, $fetchmode = PDO::FETCH_ASSOC) {
        $this->Init($query, $params);
        $resultRow = $this->sQuery->fetch($fetchmode);
        $this->rowCount = $this->sQuery->rowCount();
        $this->columnCount = $this->sQuery->columnCount();
        $this->sQuery->closeCursor();
        return $resultRow;
    }


    public function single($query, $params = null) {
        $this->Init($query, $params);
        return $this->sQuery->fetchColumn();
    }


    private function ExceptionLog($message, $sql = "") {
        $exception = 'Unhandled Exception. <br />';
        $exception .= $message;
        $exception .= "<br /> You can find the error back in the log.";

        if (!empty($sql)) {
            $message .= "\r\nRaw SQL : " . $sql;
        }
        $this->log->write($message, $this->DBName . md5($this->DBPassword));
        //Prevent search engines to crawl
        header("HTTP/1.1 500 Internal Server Error");
        header("Status: 500 Internal Server Error");
        return $exception;
    }


    abstract public function select($table, $condition = array());

    abstract public function insert($table, $p_arr);

    abstract public function update($table, $p_arr, $condition);

    abstract public function delete($table, $condition);

    abstract public function custom($query);

    abstract public function transaction(array $query, array $params = array());
}