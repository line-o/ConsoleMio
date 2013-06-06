<?php

require_once 'ConsoleMio.php';

$console = new ConsoleMio();

// colors
$console->group('test colors');
$console->log('log');
$console->debug('debug');
$console->info('info');
$console->warn('warn');
$console->error('error');
$console->endGroup();

// object
$object = new StdClass();
$object->boolProperty = true;
$object->intProperty  = 1;
$object->floatProperty  = 0.12345678;
$object->stringProperty  = "escaped? 'üöß\u00f6'";
$object->arrayProperty  = range(0, 3);
$object->nullProperty  = null;
$object->falseProperty  = false;

$console->group('test object');
$console->log($object);
$console->endGroup();

$console->group('test multiple');
$console->info($object, array("key"=>"value"), "and another one", 1, false, null, -1123.123*1000000);
$console->endGroup();

$console->group('test noflags');
$console->setEncoderOptions(0);
$console->info($object, array("key"=>"value"), "and another one", 1, false, null, -1123.123*1000000);
$console->endGroup();


