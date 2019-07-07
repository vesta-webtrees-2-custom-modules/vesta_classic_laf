<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('CompactThemes\\', __DIR__);

$loader->register();

