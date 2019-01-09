<?php
/**
 * Created by PhpStorm.
 * User: rafael.hirooka
 * Date: 28/12/2018
 * Time: 13:54
 */

namespace App\Classes\v1;


use Composer\Autoload\ClassLoader;

class SendSocket extends \Thread {

    private $program;

    private $json;

    private $loader;

    public function __construct(ClassLoader $loader, AbstractProgram $program, array $json) {
        $this->program = $program;
        $this->json = $json;
        $this->loader = $loader;
    }

    public function run() {
        try {
            $this->loader->register();

            $socket = socket_create(AF_INET, SOCK_STREAM, 0);
            $con = @socket_connect($socket, '192.168.1.53', 25003);

            if ($socket !== false && $con !== false) {
                socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec" => 30, "usec" => 0));

                $string = json_encode($this->json);
                $string .= "\n";

                if ($string !== false) {
                    @socket_write($socket, $string, strlen($string));
                    $response = @socket_read($socket, 1024);

                    if ($response == false)
                        throw new \Exception(socket_strerror(socket_last_error()));


                    $response = json_decode($response);
                    if ($response->status == '500') {
                        if (is_array($response)) {
                            $response = json_encode($response);
                            throw new \Exception('Erro ao enviar a mensagem ' . $response);
                        } else {

                        }

                    }

                } else {
                    throw new \Exception('Mensagem invalida');
                }
            } else {
                throw new \Exception(socket_strerror(socket_last_error()));
            }

            if ($socket !== false) {
                socket_clear_error($socket);
                socket_close($socket);
            }


        } catch (\Exception $e) {
            $this->program->getLogger()->log('error', $e->getMessage() . ' File: ' . $e->getFile() . '. Line: ' . $e->getLine());
            $this->program->sendContingency($this->json->message);
        }
    }
}