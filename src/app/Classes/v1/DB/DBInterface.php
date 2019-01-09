<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 03/10/2017
 * Time: 14:01
 */


interface DBInterface {
    /*
     * Connect to the database
     */
    public function Connect();
    /*
     * Close connection
     */
    public function CloseConnection();

    /*
     * Last inserted id
     */
    public function lastInsertId();
}