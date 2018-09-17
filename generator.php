<?php

use itnovum\metrics\generator\GeneratorCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$GeneratorCommand = new GeneratorCommand();

$app = new Application();
$app->add($GeneratorCommand);
$app->setDefaultCommand($GeneratorCommand->getName());
$app->run();


/*$CrateDB = new \Crate\PDO\PDO('crate:dziegler.oitc.itn:4200', null, null, null);
$stm = $CrateDB->prepare('select mountain from sys.summits order by height desc limit 5000');
*/
