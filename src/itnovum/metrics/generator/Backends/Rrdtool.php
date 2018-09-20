<?php


namespace itnovum\metrics\generator\Backends;


use itnovum\metrics\generator\Config;

class Rrdtool implements BackendInterface {

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

        foreach ($metrics as $metric) {
            $hostname = $this->replaceIllegalCharacters($metric['hostname']);
            $serviceDesc = $this->replaceIllegalCharacters($metric['service_description']);
            $label = $this->replaceIllegalCharacters($metric['label']);


            $start = microtime(true);
            if (!is_dir($this->config['rrdtool']['path'] . DIRECTORY_SEPARATOR . $hostname)) {
                mkdir($this->config['rrdtool']['path'] . DIRECTORY_SEPARATOR . $hostname);
            }

            $rrdFile = $this->config['rrdtool']['path'] . DIRECTORY_SEPARATOR . $hostname . DIRECTORY_SEPARATOR . $serviceDesc . '_' . $label . '.rrd';
            if (!file_exists($rrdFile)) {
                $options = [];
                $options[] = 'RRA:AVERAGE:0.5:1:576000';
                $options[] = 'RRA:MAX:0.5:1:576000';
                $options[] = 'RRA:MIN:0.5:1:576000';
                $options[] = 'DS:1:GAUGE:8460:U:U';
                $options[] = '--start=' . $metric['timestamp'];
                $options[] = '--step=60';

                if (!rrd_create($rrdFile, $options)) {
                    print_r('Error on creating RRD');
                    echo PHP_EOL;
                    print_r(rrd_error());
                    echo PHP_EOL;
                }
            }

            //Update RRD
            $options = [];
            $options[] = $metric['timestamp'];
            $options[] = $metric['value'];
            if (!rrd_update($rrdFile, [implode(':', $options)])) {
                print_r('Error on updating RRD');
                echo PHP_EOL;
                print_r(rrd_error());
                echo PHP_EOL;
            }


            $end = microtime(true);
            return $end - $start;
        }


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
