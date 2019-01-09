<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 13/07/2018
 * Time: 11:28
 */

namespace App\Classes\v1\DB;


use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Driver\CursorId;
use MongoDB\Driver\Query;

class MongoController {
    public static $manager;
    public static $bulk;

    private $db;
    private $host;
    private $port;
    private $collection;

    public function __construct($host = MONGO_HOST, $port = MONGO_PORT) {
        try {
            $this->host = $host;
            $this->port = $port;

            self::$manager = new \MongoDB\Driver\Manager('mongodb://' . $host . ':' . $port);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getManager() {
        if (self::$manager == NULL) {
            self::$manager = new \MongoDB\Driver\Manager('mongodb://' . $this->host . ':' . $this->port);
        }

        return self::$manager;
    }


    public function insertBatch(array $batch) {
        try {
            self::$bulk = new \MongoDB\Driver\BulkWrite;

            if (!empty($batch)) {
                foreach ($batch as $item) {
                    self::$bulk->insert($item);
                }


                $result = $this->getManager()->executeBulkWrite($this->db . '.' . $this->collection, self::$bulk);

                return $result;
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(array $condition, array $values) {
        try {
            self::$bulk = new \MongoDB\Driver\BulkWrite;

            if (!empty($condition)) {
                self::$bulk->update(
                    $condition,
                    ['$set' => $values],
                    ['multi' => false, 'upsert' => false]
                );


                $result = $this->getManager()->executeBulkWrite($this->db . '.' . $this->collection, self::$bulk);

                return $result;
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete(array $condition) {
        try {
            self::$bulk = new \MongoDB\Driver\BulkWrite;

            if (!empty($condition)) {
                self::$bulk->delete($condition, ['limit' => 1]);


                $result = $this->getManager()->executeBulkWrite($this->db . '.' . $this->collection, self::$bulk);

                return $result;
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function find($filter = [], $options = []) {
        try {
            if ($this->collection != NULL) {

                $query = new Query($filter, $options);

                $r = $this->getManager()->executeQuery($this->db . '.' . $this->collection, $query);

                return $r;
            } else {
                throw new \Exception("Nenhuma collection selecionada.");
            }


        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function command($cmd = []) {
        try {
            if ($this->collection != NULL) {

                $cmd['cursor'] = new \stdClass();
                $command = new Command($cmd);

                $cursor = $this->getManager()->executeCommand($this->db, $command);

                return $cursor;
            } else {
                throw new \Exception("Nenhuma collection selecionada.");
            }


        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }




    public function setDB($db) {
        $this->db = $db;
    }

    public function setCollection($collection) {
        $this->collection = $collection;
    }
}