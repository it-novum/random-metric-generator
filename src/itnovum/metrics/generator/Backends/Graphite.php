<?php


namespace itnovum\metrics\generator\Backends;


use itnovum\metrics\generator\Config;

class Graphite implements BackendInterface {

    /**
     * @var array
     */
    private $config;

    /**
     * @var resource
     */
    private $socket;


    public function __construct(Config $Config) {
        $this->config = $Config->getConfig();
    }


    public function connect() {

    }

    /**
     * @param $metrics
     * @return double
     */
    public function save($metrics) {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, IPPROTO_IP);
        if (!@socket_connect($this->socket, $this->config['graphite']['host'], $this->config['graphite']['port'])) {
            print_r('Connection error!');
            echo PHP_EOL;
        }

        $start = microtime(true);
        try {
            foreach ($metrics as $metric) {
                $data = $this->buildKey($metric);
                $data .= PHP_EOL;
                socket_send($this->socket, $data, strlen($data), 0);
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
            echo PHP_EOL;
        }
        $end = microtime(true);

        socket_close($this->socket);

        return $end - $start;
    }

    private function buildKey($metric) {
        return sprintf(
            '%s.%s.%s.%s %s %s',
            $this->replaceIllegalCharacters('statusengine'),
            $this->replaceIllegalCharacters($metric['hostname']),
            $this->replaceIllegalCharacters($metric['service_description']),
            $this->replaceIllegalCharacters($metric['label']),
            $metric['value'],
            $metric['timestamp']
        );
    }

    public function replaceIllegalCharacters($str) {
        return preg_replace('/[^a-zA-Z^0-9\-\.]/', '_', $str);
    }

}
