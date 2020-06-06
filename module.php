<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Fisharebest\Webtrees\Webtrees;

//webtrees major version switch
if (defined("WT_MODULES_DIR")) {
  //this is a webtrees 2.x module. it cannot be used with webtrees 1.x. See README.md.
  return;
} else {
  $modulesPath = Webtrees::MODULES_PATH;
}

//add our own, and other (vesta_common, ...), dependencies
//note: in the current module system, this would happen anyway because all module.php's are executed
//whenever a single module is loaded (assuming these autoload.php's are called by the respective module.php's)
//so we aren't loading 'too much' here.
//DO NOT USE $file HERE! see Module.loadModule($file) - we must not change that var!
foreach (glob(Webtrees::ROOT_DIR . $modulesPath . '*/autoload.php') as $autoloadFile) {
  require_once $autoloadFile;
}

return app(ClassicLAFModule::class);
