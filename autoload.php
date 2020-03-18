<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('CompactThemes\\', __DIR__);
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");

$loader->addPsr4('Vesta\\ControlPanel\\', __DIR__ . "/controlpanel");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model/elements");

$loader->register();
