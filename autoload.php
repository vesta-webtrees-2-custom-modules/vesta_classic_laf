<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('Cissee\\Webtrees\\Module\\ClassicLAF\\', __DIR__);
$loader->addPsr4('Cissee\\Webtrees\\Module\\ClassicLAF\\Factories\\', __DIR__ . "/Factories");
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");
$loader->addPsr4('Cissee\\WebtreesExt\\CommonMark\\', __DIR__ . "/patchedWebtrees/CommonMark");
$loader->addPsr4('Cissee\\WebtreesExt\\Http\\RequestHandlers\\', __DIR__ . "/patchedWebtrees/Http/RequestHandlers");

$loader->addPsr4('IvoPetkov\\', __DIR__ . "/IvoPetkov");
$loader->addPsr4('IvoPetkov\HTML5DOMDocument\Internal\\', __DIR__ . "/IvoPetkov/HTML5DOMDocument/Internal");

$loader->register();
