<?php


namespace itnovum\metrics\generator\Backends;


use itnovum\metrics\generator\Config;

class DevNull implements BackendInterface {

    /**
     * @var array
     */
    private $config;

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
        return 0;
    }

}
