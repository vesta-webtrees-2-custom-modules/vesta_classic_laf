<?php

namespace Cissee\WebtreesExt;

//requires the develop-branch
/*
use Fisharebest\Webtrees\Contracts\IndividualFactoryInterface;
use Fisharebest\Webtrees\Factories\IndividualFactory;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Individual;

class CustomIndividualFactory extends IndividualFactory implements IndividualFactoryInterface {

    public function make(string $xref, Tree $tree, string $gedcom = null): ?Individual
    {    
        return $this->cache->remember(__CLASS__ . $xref . '@' . $tree->id(), function () use ($xref, $tree, $gedcom) {
            $gedcom  = $gedcom ?? $this->gedcom($xref, $tree);
            $pending = $this->pendingChanges($tree)->get($xref);

            if ($gedcom === null && $pending === null) {
                return null;
            }

            $xref = $this->extractXref($gedcom ?? $pending, $xref);

            return new \Cissee\WebtreesExt\Individual($xref, $gedcom ?? '', $pending, $tree);
        });
    }
  
    public function new(string $xref, string $gedcom, ?string $pending, Tree $tree): Individual
    {
        return new \Cissee\WebtreesExt\Individual($xref, $gedcom, $pending, $tree);
    }
}
*/

