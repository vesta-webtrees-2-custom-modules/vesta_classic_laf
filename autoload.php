<?php

use Composer\Autoload\ClassLoader;

$loader = new ClassLoader();
$loader->addPsr4('CompactThemes\\', __DIR__);
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");

$loader->addPsr4('Vesta\\ControlPanel\\', __DIR__ . "/controlpanel");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model/elements");

$classMap = array();
$extend = !class_exists("Fisharebest\Webtrees\Individual", false);

if ($extend) {
  //explicitly load webtrees replacements so that the original files aren't autoloaded
  $classMap["Fisharebest\Webtrees\Individual"] = __DIR__ . '/replacedWebtrees/Individual.php';
}

$loader->addClassMap($classMap);        
$loader->register(true); //prepend in order to override definitions from default class loader

