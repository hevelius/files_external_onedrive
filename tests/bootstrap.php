<?php
//require_once __DIR__ . '/../../../tests/bootstrap.php';
//require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../../../lib/base.php';

\OC::$composerAutoloader->addPsr4('Test\\', OC::$SERVERROOT . '/tests/lib/', true);
\OC::$composerAutoloader->addPsr4('Tests\\', OC::$SERVERROOT . '/tests/', true);

OC_App::loadApp('files_external_onedrive');

OC_Hook::clear();