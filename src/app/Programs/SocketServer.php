<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 27/12/2018
 * Time: 16:00
 */

namespace App\Programs;


use App\Classes\v1\AbstractProgram;

class SocketServer extends AbstractProgram {

    private $socket;

    protected function main() {
        try {
            $this->setName('socketServer');

            $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);

            socket_bind($this->socket, '0.0.0.0', SOCKET_PORT);
            socket_listen($this->socket, 3);
            socket_set_nonblock($this->socket);
            socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);

            $clients = array($this->socket);

            $message = $write = $except = NULL;
            do {
                try {
                    $read = $clients;

                    if (socket_select($read, $write, $except, NULL) < 1)
                        continue;


                    if (in_array($this->socket, $read)) {
                        try {
                            $newsock = socket_accept($this->socket);

                            $clients[] = $newsock;
                            $message[] = $newsock;

                            $key = array_search($this->socket, $read);
                            unset($read[$key]);

                        } catch (\Exception $e) {
                            $this->logger->log('error', $e->getMessage() . '. File: ' . $e->getFile() . '. Line: ' . $e->getLine());
                        }
                    }


                    foreach ($message as $k => $read_sock) {
                        socket_getpeername($read_sock, $ip, $port);
                        socket_write($read_sock, '200', 3);

                        unset($message[$k]);
                        $key = array_search($read_sock, $clients);
                        unset($clients[$key]);
                    }

                    $message = array_values($message);
                    $clients = array_values($clients);


                } catch (\Exception $e) {
                    $this->logger->log('error', $e->getMessage() . '. File: ' . $e->getFile() . '. Line: ' . $e->getLine());
                }

            } while(true);


            socket_close($this->socket);



        } catch (\Exception $e) {
            $this->logger->log('error', $e->getMessage() . '. File: ' . $e->getFile() . '. Line: ' . $e->getLine());
        }
    }

    public function call() {
        $this->main();
    }
}