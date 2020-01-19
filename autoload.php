<?php

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Webtrees;

$loader = new ClassLoader();
$loader->addPsr4('CompactThemes\\', __DIR__);
$loader->addPsr4('Cissee\\WebtreesExt\\', __DIR__ . "/patchedWebtrees");

$loader->addPsr4('Vesta\\ControlPanel\\', __DIR__ . "/controlpanel");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model");
$loader->addPsr4('Vesta\\ControlPanel\\Model\\', __DIR__ . "/controlpanel/model/elements");

$loader->register();

//TODO this is a hack (doesn't work if module is renamed)
//we need a more generic solution for replacing webtrees core classes!
//
//if vesta shared places is also used, autoload that first 
//(Individual extends GedcomRecord)
foreach (glob(Webtrees::ROOT_DIR . Webtrees::MODULES_PATH . 'vesta_shared_places/autoload.php') as $autoloadFile) {
  require_once $autoloadFile;
}

$extend = !class_exists("Fisharebest\Webtrees\Individual", false);
        
if ($extend) {
  //explicitly load webtrees replacements so that the original files aren't autoloaded
  require_once __DIR__ . '/replacedWebtrees/Individual.php';
}  
