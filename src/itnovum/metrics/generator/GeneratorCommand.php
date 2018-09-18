<?php


namespace itnovum\metrics\generator;


use itnovum\metrics\generator\Backends\Crate;
use itnovum\metrics\generator\Backends\DevNull;
use itnovum\metrics\generator\Backends\Elasticsearch;
use itnovum\metrics\generator\Backends\Graphite;
use itnovum\metrics\generator\Backends\Mysql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorCommand extends Command {

    private $supportedBackends = [
        'mysql',
        'graphite',
        'cratedb',
        'elasticsearch',
        'devnull'
    ];

    protected function configure() {
        $this->setName('metrics:generator')
            ->setDescription('Will generate random metrics');

        $this->addOption('bulk-size', 'b', InputOption::VALUE_OPTIONAL, 'Bulk size of each query', 1000);
        $this->addOption('num-hosts', null, InputOption::VALUE_OPTIONAL, 'Number of hosts', 100);
        $this->addOption('backend', null, InputOption::VALUE_OPTIONAL, 'Storage backend: ' . implode(', ', $this->supportedBackends), 'mysql');
        $this->addOption('parst', null, InputOption::VALUE_OPTIONAL, 'Generate history data (timespan defined in config as days)', false);


    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $fakeParst = false;
        if ($input->getOption('parst') === null || $input->getOption('parst') === 'true') {
            $fakeParst = true;
        }

        $Config = new Config();
        $Mountains = new Mountains();
        $hosts = [];
        for ($i = 0; $i < $input->getOption('num-hosts'); $i++) {
            $hosts[] = new FakeHost($Mountains->getRandomMountain());
        }

        $backendName = $input->getOption('backend');

        switch ($backendName) {
            case 'cratedb':
                $Backend = new Crate($Config);
                break;

            case 'mysql':
                $Backend = new Mysql($Config);
                break;

            case 'elasticsearch':
                $Backend = new Elasticsearch($Config);
                break;

            case 'graphite':
                $Backend = new Graphite($Config);
                break;

            default:
                //Stuff
                $Backend = new DevNull($Config);
                break;
        }

        $Backend->connect();

        $metrics = [];
        $metricsCount = 0;

        $bulkSize = (int)$input->getOption('bulk-size');

        //Fake history data
        if ($fakeParst === true) {
            $checkInterval = (int)$Config->config['check_interval'];
            if ($checkInterval <= 0) {
                $checkInterval = 1;
            }
            $start = time() - ($Config->config['days'] * 60 * 60 * 24);
            $end = time();

            $output->writeln(sprintf('<comment>Start date: %s</comment>', date('d.m.Y H:i:s', $start)));
            $output->writeln(sprintf('<comment>End date: %s</comment>', date('d.m.Y H:i:s', $end)));
            $output->writeln(sprintf(
                '<comment>Total data points to generate: %s</comment>',
                number_format(
                    ($end - $start / $checkInterval) * $input->getOption('num-hosts') * sizeof($hosts[0]->getMetrics())
                )));


            /** @var FakeHost $host */
            while ($start < $end) {
                foreach ($hosts as $host) {
                    foreach ($host->getMetrics($start) as $metric) {
                        $metrics[] = $metric;
                        $metricsCount++;

                        if ($metricsCount >= $bulkSize) {
                            //Random shuffle metrics array to make it more realistic
                            shuffle($metrics);

                            $took = $Backend->save($metrics);
                            /*$output->writeln(sprintf(
                                '<info>Pushed %s records to backend %s (took %s ms)</info>',
                                sizeof($metrics),
                                $backendName,
                                round($took, 3)
                            ));*/
                            $metrics = [];
                            $metricsCount = 0;
                        }

                    }
                }


                if (sizeof($metrics) > 0) {
                    //Random shuffle metrics array to make it more realistic
                    shuffle($metrics);
                    $took = $Backend->save($metrics);
                    /*$output->writeln(sprintf(
                        '<info>Pushed %s records to backend %s (took %s ms)</info>',
                        sizeof($metrics),
                        $backendName,
                        round($took, 3)
                    ));*/
                    $metrics = [];
                    $metricsCount = 0;
                }


                $start = $start + ($checkInterval * 60);

                if ($start % 10000) {
                    $output->write(sprintf(
                        "<info>Current date: %s</info>              \r",
                        date('d.m.Y H:i:s', $start)
                    ));
                }
            }
        }

        //Just generate random data
        if ($fakeParst === false) {
            /** @var FakeHost $host */
            foreach ($hosts as $host) {
                foreach ($host->getMetrics() as $metric) {
                    $metrics[] = $metric;
                    $metricsCount++;

                    if ($metricsCount >= $bulkSize) {
                        //Random shuffle metrics array to make it more realistic
                        shuffle($metrics);

                        $took = $Backend->save($metrics);
                        $output->writeln(sprintf(
                            '<info>Pushed %s records to backend %s (took %s ms)</info>',
                            sizeof($metrics),
                            $backendName,
                            round($took, 3)
                        ));
                        $metrics = [];
                        $metricsCount = 0;
                    }

                }
            }


            if (sizeof($metrics) > 0) {
                //Random shuffle metrics array to make it more realistic
                shuffle($metrics);
                $took = $Backend->save($metrics);
                $output->writeln(sprintf(
                    '<info>Pushed %s records to backend %s (took %s ms)</info>',
                    sizeof($metrics),
                    $backendName,
                    round($took, 3)
                ));
                $metrics = [];
                $metricsCount = 0;
            }
        }


    }

}
