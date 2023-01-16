<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
use Fisharebest\Webtrees\Http\RequestHandlers\AddSpouseToIndividualPage;

class AddSpouseToIndividualPageExt extends AddSpouseToIndividualPage {

    public function __construct() {
        parent::__construct(
            new GedcomEditServiceExt2(true));
                
        //explicitly register in order to re-use in views where we cannot pass via variable
        app()->instance(GedcomEditServiceExt2::class, new GedcomEditServiceExt2(true));
    }
}
