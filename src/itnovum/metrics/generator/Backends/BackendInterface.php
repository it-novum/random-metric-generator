<?php


namespace itnovum\metrics\generator\Backends;


use itnovum\metrics\generator\Config;

interface BackendInterface {

    /**
     * BackendInterface constructor.
     * @param Config $Config
     */
    public function __construct(Config $Config);

    public function connect();

    /**
     * @param array $metrics
     * @return double
     */
    public function save($metrics);

}
