<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Tree;
use function app;

class FamilyExt extends Family {

  public function __construct(
            string $xref, 
            string $gedcom, 
            ?string $pending, 
            Tree $tree)
    {
        parent::__construct($xref, $gedcom, $pending, $tree);
    }
    
  public function fullName(): string
    {
        //[RC] adjusted: logic is configurable
        $handler = app(FamilyNameHandler::class);
        
        $full = parent::fullName();
        $full = $handler->addXref($full, $this->xref());
        return $full;
    }  
}
