<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

class CustomTreeService extends TreeService {
  
  protected $module;
  
  public function __construct($module) {
    $this->module = $module;
  }
  
  public function all(): Collection {
    $self = $this;
    
    //not efficient (each tree constructed one as Tree, and once as CustomTree) but easiest this way    
    return parent::all()->map(static function (Tree $tree) use ($self): Tree {
        return CustomTree::create($tree, $self->module);
    });
  }
}

