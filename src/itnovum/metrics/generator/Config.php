<?php

namespace itnovum\metrics\generator;

use Symfony\Component\Yaml\Yaml;

class Config {

    /**
     * @var array
     */
    public $config = [];


    public function __construct() {
        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.yml';
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('Config file "%s" not found!', $file));
        }
        $this->config = Yaml::parse(file_get_contents($file));
    }

    /**
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

}