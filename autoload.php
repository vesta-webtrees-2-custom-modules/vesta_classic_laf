<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('Cissee\\Webtrees\\Module\\ClassicLAF\\', __DIR__);
$loader->addPsr4('Cissee\\Webtrees\\Module\\ClassicLAF\\Factories\\', __DIR__ . "/Factories");
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");
$loader->addPsr4('Cissee\\WebtreesExt\\Http\\RequestHandlers\\', __DIR__ . "/patchedWebtrees/Http/RequestHandlers");
$loader->register();
