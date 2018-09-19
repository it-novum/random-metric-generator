<?php


namespace itnovum\metrics\generator\Backends;


use Elasticsearch\ClientBuilder;
use itnovum\metrics\generator\Config;

class Elasticsearch implements BackendInterface {

    /**
     * @var array
     */
    private $config;


    private $Client;

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

        $index = sprintf('statusengine-metric-%s', date('Y.m.d', $metrics[0]['timestamp']));

        $bulkData = [];

        foreach ($metrics as $metric) {
            $bulkData[] = [
                'index' => [
                    '_index' => $index,
                    '_type'  => 'metric',
                ]
            ];

            $bulkData[] =
                [
                    '@timestamp'          => ($metric['timestamp'] * 1000),
                    'value'               => $metric['value'],
                    'hostname'            => $metric['hostname'],
                    'service_description' => $metric['service_description'],
                    'metric'              => $metric['label']
                ];
        }


        $Client = $this->Client = $Client = ClientBuilder::create()->setHosts([
            sprintf('%s:%s', $this->config['elasticsearch']['host'], $this->config['elasticsearch']['port'])
        ])->build();

        $start = microtime(true);
        try {
            $response = $Client->bulk(['body' => $bulkData]);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            echo PHP_EOL;
        }
        $end = microtime(true);
        return $end - $start;
    }

}
