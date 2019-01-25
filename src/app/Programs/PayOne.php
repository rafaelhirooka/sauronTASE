<?php
namespace App\Programs;

use App\Classes\v1\AbstractProgram;
use App\Classes\v1\DB\DBController;
use PHPMailer\PHPMailer\PHPMailer;
use Phpml\ModelManager;

class PayOne extends AbstractProgram {

    /**
     * Time in seconds to know how long the function will sleep to be called again
     * @var int time
     */
    protected $time = 600;

    private $last_id = NULL;

    private $last_status = 1;

    private $modelPath = __DIR__ . '/modelPayone.txt';

    private $phones = ['5519996941420', '5519992291550'];

    /**
     * Write the logic of program
     */
    protected function main() {
        try {
            $db = new DBController('192.168.1.19', 'ucs_admin', 'ucs12!@', 'payonedb');

            // First time running or not
            if ($this->last_id == NULL) {
                $date = new \DateTime();
                $date->sub(new \DateInterval('PT10M'));

                $query = "SELECT ID, INTERNAL_RESPONSE_CODE, DATE_TIME FROM TRANSACTION_LOG";
                $query .= " WHERE DATE_TIME >= '" . $date->format('Ymd H:i:s') . "'";
                $query .= " ORDER BY ID ASC";
            } else {
                $query = "SELECT ID, INTERNAL_RESPONSE_CODE, DATE_TIME FROM TRANSACTION_LOG";
                $query.= " WHERE ID > " . $this->last_id;
                $query .= " ORDER BY ID ASC";
            }

            $transactions = $db->custom($query);

            $db->CloseConnection();

            if (!empty($transactions)) {
                $this->last_id = $transactions[count($transactions) - 1]['ID'];

                $errors = [];
                foreach ($transactions as $transaction) {
                    if ($transaction['INTERNAL_RESPONSE_CODE'] == 91) {
                        $errors[] = $transaction;
                    }
                }

                if (!empty($errors)) {
                    $modelManager = new ModelManager();
                    $restoredClassifier = $modelManager->restoreFromFile($this->modelPath);

                    $total = count($errors);
                    $predict = $restoredClassifier->predict([$errors[0]['DATE_TIME'], $total]);

                    if ($this->last_status != $predict) {
                        switch ($predict) {
                            case 1:
                                $message = 'Situação normal com Payone Transaction Log';
                                break;

                            case 2:
                                $message = 'Situação amarela com Payone Transaction Log. Nos últimos 10 minutos tivemos ' . $total . ' transações com erro 91';
                                break;

                            case 3:
                                $message = 'Situação vermelha com Payone Transaction Log. Nos últimos 10 minutos tivemos ' . $total . ' transações com erro 91';
                                break;

                            default:
                                $message = 'Mensagem indefinida';
                                break;
                        }

                        $this->last_status = $predict;

                        $to_send = [];
                        foreach ($this->phones as $k => $phone) {
                            $to_send[$k]['phone'] = $phone;
                            $to_send[$k]['message'] = $message;
                        }


                        $this->send($to_send);
                    }
                }
            }


        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function send(array $json) {
        try {
            $mail = new PHPMailer();
            //$mail->SMTPDebug = 2;
            $mail->isSMTP();

            $mail->Host = 'smtp.office365.com';
            $mail->Port       = 587;
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth   = true;
            $mail->Username = 'webmaster@vegascard.com.br';
            $mail->Password = 'grupovegas2@18';
            $mail->setFrom('webmaster@vegascard.com.br', 'TI');
            $mail->addAddress('ti@vegascard.com.br');
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $mail->Subject = 'Payone Monitoramento';
            $mail->Body    = $json[0]['message'];
            $mail->AltBody = $json[0]['message'];

            $mail->send();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}