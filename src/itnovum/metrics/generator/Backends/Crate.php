<?php


namespace itnovum\metrics\generator\Backends;


use Crate\PDO\PDO;
use itnovum\metrics\generator\Config;

class Crate implements BackendInterface {

    /**
     * @var array
     */
    private $config;

    /**
     * @var PDO
     */
    private $connection;

    public function __construct(Config $Config) {
        $this->config = $Config->getConfig();
    }


    public function connect() {
        $this->connection = new PDO(
            sprintf(
                'crate:%s:%s',
                $this->config['cratedb']['host'],
                $this->config['cratedb']['port']
            ),
            null, null, null);
    }

    /**
     * @param $metrics
     * @return double
     */
    public function save($metrics) {

        $baseQuery = 'INSERT INTO statusengine_perfdata (hostname, service_description, label, timestamp, timestamp_unix, value, unit)VALUES%s';
        $baseValues = '(?, ?, ?, ?, ?, ?, ?)';
        $values = [];
        for ($i = 1; $i <= sizeof($metrics); $i++) {
            $values[] = $baseValues;
        }
        $query = $this->connection->prepare(sprintf($baseQuery, implode(', ', $values)));


        $i = 1;
        foreach ($metrics as $metric) {
            $query->bindValue($i++, $metric['hostname']);
            $query->bindValue($i++, $metric['service_description']);
            $query->bindValue($i++, $metric['label']);
            $query->bindValue($i++, ($metric['timestamp'] * 1000));
            $query->bindValue($i++, $metric['timestamp']);
            $query->bindValue($i++, (double)$metric['value']);
            $query->bindValue($i++, $metric['unit']);
        }

        $start = microtime(true);
        try {
            $query->execute();
        }catch (\Exception $e){
            print_r($e->getMessage());
            echo PHP_EOL;
        }
        $end = microtime(true);
        return $end - $start;
    }

}
