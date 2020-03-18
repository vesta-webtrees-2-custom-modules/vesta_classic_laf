<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Services\TreeService;
use Illuminate\Support\Collection;

class CustomTreeService extends TreeService {
  
  public function all(): Collection {
    //error_log("CustomTreeService");
    return parent::all();
  }
}

