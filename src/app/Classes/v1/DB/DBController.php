<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 09/10/2017
 * Time: 15:10
 */

namespace App\Classes\v1\DB;

class DBController extends DBC {


    public function __construct($host, $user, $pass, $name, $type = 'sqlsrv') {
        parent::__construct($host, $user, $pass, $name, $type);
    }


    public function insert($table, $p_arr) {
        try {
            /* Building the query */
            $query = "INSERT INTO {$table} (";
            $i = 1;
            foreach ($p_arr as $k => $v) {
                $query .= trim($k);
                if ($i < count($p_arr))
                    $query .= ", ";
                $i++;
            }
            $query .= ") VALUES (";
            $i = 1;
            foreach ($p_arr as $k => $v) {
                $query .= "?";
                if ($i < count($p_arr))
                    $query .= ", ";
                $i++;
            }
            $query .= ")";
            /* Building query end */
            /* Try to insert */
            $r = $this->query($query, $p_arr);
            if ($r > 0)
                return true;
            else
                throw new \Exception("Houve alguma coisa ao tentar inserir este registro");

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update($table, $p_arr, $condition, $only_query = false) {
        try {
            if (!empty($p_arr)) {
                if (!empty($condition)) {
                    /* Building the query */
                    $query = "UPDATE {$table} SET ";
                    $i = 1;
                    foreach ($p_arr as $k => $v) {

                        if ($v == "GETDATE") {
                            $query .= trim($k) . " = GETDATE()";
                        } else {
                            $query .= trim($k) . " = ?";
                        }


                        if ($i < count($p_arr))
                            $query .= ", ";
                        $i++;
                    }
                    $query .= " WHERE ";
                    // Put the update condition
                    for ($i = 0; $i < count($condition); $i++) {
                        $query .= $condition[$i]["c"] . $condition[$i]["o"] . "?";
                        $p_arr[] = $condition[$i]["v"];
                        if (isset($condition[$i]["next"]) && isset($condition[$i + 1]))
                            $query .= " " . $condition[$i]["next"] . " ";
                    }
                    /* Building query end */

                    // Just need to return query string
                    if ($only_query) {
                        return $query;
                    }


                    /* Try to update */

                    $r = $this->query($query, $p_arr);
                    if ($r > 0)
                        return true;
                } else {
                    throw new \Exception("Parâmetro vazio");
                }
            } else {
                throw new \Exception("Parâmetro vazio");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function select($table, $condition = array()) {
        try {

            // Start the array
            $params = array();
            /* Build the query */
            $query = "SELECT * FROM {$table} ";

            // if isset condition
            if (!empty($condition)) {
                $query .= "WHERE ";

                for ($i = 0; $i < count($condition); $i++) {
                    $query .= $condition[$i]["c"] . $condition[$i]["o"] . "?";
                    $params[] = $condition[$i]["v"];
                    if (isset($condition[$i]["next"]) && isset($condition[$i + 1]))
                        $query .= " " . $condition[$i]["next"] . " ";
                }
            }

            $r = $this->query($query, $params);

            if (!empty($r)) {

                // Return the array
                return $r;
            } else {
                return array();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($table, $condition) {
        try {

            // Start the array
            $params = array();
            /* Build the query */
            $query = "DELETE FROM {$table} ";

            // if isset condition
            if (!empty($condition)) {
                $query .= " WHERE ";

                for ($i = 0; $i < count($condition); $i++) {
                    $query .= $condition[$i]["c"] . $condition[$i]["o"] . "?";
                    $p_arr[] = $condition[$i]["v"];
                    if (isset($condition[$i]["next"]) && isset($condition[$i + 1]))
                        $query .= " " . $condition[$i]["next"] . " ";
                }
            }

            $r = $this->query($query, $params);


            if (!empty($r)) {

                // Return the array
                return $r;
            } else {
                throw new \Exception("Nenhum produto encontrado.");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function select2($tables, $columns, $conditions, $joins = array()) {
        try {
            // Start the array
            $params = array();

            $query = "SELECT ";

            if (!empty($columns)) {
                foreach ($columns as $k => $column) {
                    $query .= $column["column"];

                    if (isset($column["alias"])) {
                        $query .= " AS " . $column["alias"];
                    }

                    if ($k < (count($columns) - 1))
                        $query .= ", ";
                    else
                        $query .= " ";

                }
            } else {
                $query .= " * ";
            }

            if (!empty($tables)) {
                $query .= " FROM ";

                foreach ($tables as $k => $table) {
                    $query .= $table;

                    if ($k < (count($tables) - 1))
                        $query .= ", ";
                    else
                        $query .= " ";

                }
            } else {
                throw new \Exception("Nenhuma tabela selecionada.");
            }


            if (!empty($conditions)) {
                $query .= "WHERE ";

                for ($i = 0; $i < count($conditions); $i++) {
                    if (strtoupper($conditions[$i]["o"]) == "LIKE") {
                        $query .= $conditions[$i]["c"] . " " . $conditions[$i]["o"] . " " . $conditions[$i]["v"];
                    } else {
                        $query .= $conditions[$i]["c"] . " " . $conditions[$i]["o"] . " " . "?";
                        $params[] = $conditions[$i]["v"];
                    }

                    if (isset($conditions[$i]["next"]) && isset($conditions[$i + 1]))
                        $query .= " " . $conditions[$i]["next"] . " ";
                }
            }


            return $query;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function custom($query) {
        try {
            $r = $this->query($query);

            return $r;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getPDO() {
        return $this->pdo;
    }

     public function transaction(array $query, array $params = array()) {
         try {
            $this->pdo->beginTransaction();

            foreach ($query as $k => $q) {
                $sth = $this->pdo->prepare($q);

                // If can't prepare the query
                if (!$sth) {
                    throw new \Exception("Erro ao preparar a query: " . json_encode($this->pdo->errorInfo()));
                }

                if (isset($params[$k]) && !empty($params[$k])) {

                    $p_arr = array();
                    // Order the array
                    foreach ($params[$k] as $v) {
                        $p_arr[] = $v;
                    }

                    if (!$sth->execute($p_arr)) {
                        throw new \Exception(json_encode($this->pdo->errorInfo()) . " Cod: " . $k . ". Parâmetros " . json_encode($params[$k]));
                    }
                } else {
                    if (!$sth->execute()) {
                        throw new \Exception(json_encode($this->pdo->errorInfo()) . " Cod: " . $k);
                    }
                }
            }

             // Commit
             $this->pdo->commit();

            return true;

         } catch (\Exception $e) {
             // Rollback
            $this->pdo->rollBack();

            throw new \Exception($e->getMessage());
         }
     }
}