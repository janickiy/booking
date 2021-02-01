<?php

namespace ReservationKit\src\Modules\Sirena\Model;

use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractClient;

class SocketClient extends AbstractClient
{
    /**
     * @const Количество повторных подключений
     */
    const CONNECTION_COUNTER = 2;

    /**
     * @const Количество секунд остановки перед тем как сделать повторный запрос
     */
    const CONNECTION_SLEEP_TIME = 1;

    /**
     * @const Флаги сообщения
     */
    const FLAG_REQUEST_ZIP          = 0x04; // Сообщение сжато с помощью zip
    const FLAG_RESPONSE_ZIP         = 0x10; // Ответ на сообщение может быть сжат с помощью zip
    const FLAG_REQUEST_SKEY         = 0x08; // Сообщение зашифровано симметричным ключом
    const FLAG_REQUEST_PUBKEY       = 0x40; // Сообщение зашифровано открытым ключом
    const FLAG_QUERY_NOT_PROCESSED  = 0x01;

    private $_socket = null;

    private $_connectionAddress = null;
    private $_connectionPort    = null;

    public function __construct($address = null, $port = null)
    {
        $this->setConnectionAddress($address);
        $this->setConnectionPort($port);
        
        $this->connect();
    }

    public function connect()
    {
        $connectionAttempts = self::CONNECTION_COUNTER;

        do {
            // Создание сокета
            if ( !$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ) {
                sleep(self::CONNECTION_SLEEP_TIME);
                continue;
            }

            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 60, 'usec' => 0));   // Таймаут чтения
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 60, 'usec' => 0));   // Таймаут записи

            // Подключение
            if ( $connection = @socket_connect($socket, $this->getConnectionAddress(), $this->getConnectionPort()) ) {
                $this->setSocket($socket);
                break;
            }

        } while (--$connectionAttempts);

        return $this->getSocket();
    }

    public function disconnect()
    {
        socket_close($this->getSocket());
    }

    public function send($request)
    {
        pr($request);

        $sirena_client_id_prod = '8274'; // prod
        $sirena_client_id_test = '1248'; // test

        $header = pack('NNN', mb_strlen($request, "8bit"), time(), time());
        $header .= pack('x32');
        $header .= pack('nCCN', $sirena_client_id_test, 0, 0, null);
        $header .= pack('x48');

        socket_write($this->getSocket(), $header . $request, strlen($header . $request));

        $this->read();
    }

    public function read()
    {
        $header = socket_read($this->getSocket(), 100);

        //pr($header);

        // Проверка, что сокет еще живой
        //$info = stream_get_meta_data($socket);
        /*
        if (0 &&$info['timed_out'] === true) {
            //$log->addError('read header timeouted');
        }
        */

        $response = $header;
        $data = @unpack('Nlength/Ntime/Nid/x32res1/nclientID/Cflag1/Cflag2/Nkey/x48res2/', $header);
        $messageLength = $data['length'];

        //stream_set_timeout($socket, 50);
        //ini_set('default_socket_timeout', 50);
        //stream_set_blocking($socket, 0);

        $needle = $messageLength;
        $buffer = '';

        while ($needle > 0 /* && !$info["timed_out"]*/) {
            $buffer .= socket_read($this->getSocket(), min($needle, 1024));
            $needle = $messageLength - mb_strlen($buffer, "8bit");

            // Проверка, что сокет еще живой
            //$info = stream_get_meta_data($socket);
            if (0 && $info['timed_out'] === true) {
                //$log->addError('read message timeouted');
            }
        }

        $response .= $buffer;

        // Вырезание бинарного заголовка
        $response = mb_substr($response, 100, intval($data['length']), "8bit");

        $this->setResponse($response);
    }

    /**
     * @return null
     */
    public function getSocket()
    {
        return $this->_socket;
    }

    /**
     * @param null $socket
     */
    public function setSocket($socket)
    {
        $this->_socket = $socket;
    }

    /**
     * @return null
     */
    public function getConnectionAddress()
    {
        return $this->_connectionAddress;
    }

    /**
     * @param null $ConnectionAddress
     */
    public function setConnectionAddress($ConnectionAddress)
    {
        $this->_connectionAddress = $ConnectionAddress;
    }

    /**
     * @return null
     */
    public function getConnectionPort()
    {
        return $this->_connectionPort;
    }

    /**
     * @param null $connectionPort
     */
    public function setConnectionPort($connectionPort)
    {
        $this->_connectionPort = $connectionPort;
    }
}