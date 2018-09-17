<?php


namespace itnovum\metrics\generator;


use itnovum\metrics\generator\Backends\Crate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorCommand extends Command {

    private $supportedBackends = [
        'mysql',
        'graphite',
        'cratedb',
        'elasticsearch'
    ];

    protected function configure() {
        $this->setName('metrics:generator')
            ->setDescription('Will generate random metrics');

        $this->addOption('bulk-size', 'b', InputOption::VALUE_OPTIONAL, 'Bulk size of each query', 1000);
        $this->addOption('num-hosts', null, InputOption::VALUE_OPTIONAL, 'Number of hosts', 100);
        $this->addOption('backend', null, InputOption::VALUE_OPTIONAL, 'Storage backend: ' . implode(', ', $this->supportedBackends), 'mysql');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $Config = new Config();
        $Mountains = new Mountains();
        $hosts = [];
        for ($i = 0; $i < $input->getOption('num-hosts'); $i++) {
            $hosts[] = new FakeHost($Mountains->getRandomMountain());
        }

        switch ($input->getOption('backend')) {
            case 'cratedb':
                $Backend = new Crate($Config);
                break;

            default:
                //Stuff
                break;
        }

        $Backend->connect();

        $metrics = [];
        $metricsCount = 0;

        $bulkSize = (int)$input->getOption('bulk-size');

        /** @var FakeHost $host */
        foreach ($hosts as $host) {
            foreach ($host->getMetrics() as $metric) {
                $metrics[] = $metric;
                $metricsCount++;

                if ($metricsCount >= $bulkSize) {
                    $took = $Backend->save($metrics);
                    $output->writeln(sprintf(
                        '<info>Pushed %s records to backend (took %s ms)</info>',
                        sizeof($metric),
                        round($took, 3)
                    ));
                    $metrics = [];
                    $metricsCount = 0;
                }

            }
        }

        if (sizeof($metrics) > 0) {
            $took = $Backend->save($metrics);
            $output->writeln(sprintf(
                '<info>Pushed %s records to backend (took %s ms)</info>',
                sizeof($metrics),
                round($took, 3)
            ));
            $metrics = [];
            $metricsCount = 0;
        }


    }

}
