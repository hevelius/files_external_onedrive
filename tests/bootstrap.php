<?php
require_once __DIR__ . '/../vendor/autoload.php';
define('PHPUNIT_RUN', 1);

require_once __DIR__ . '/../../../lib/base.php';

\OC::$composerAutoloader->addPsr4('Test\\', OC::$SERVERROOT . '/tests/lib/', true);
\OC::$composerAutoloader->addPsr4('Tests\\', OC::$SERVERROOT . '/tests/', true);

if (!class_exists('PHPUnit_Framework_TestCase')) {
    require_once('PHPUnit/Autoload.php');
}

OC_Hook::clear();
