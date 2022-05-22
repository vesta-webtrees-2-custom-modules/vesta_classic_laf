<?php

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Contracts\IndividualFactoryInterface;
use Fisharebest\Webtrees\Factories\IndividualFactory;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;

class CustomIndividualFactory extends IndividualFactory implements IndividualFactoryInterface {
    
    private const TYPE_CHECK_REGEX = '/^0 @[^@]+@ ' . Individual::RECORD_TYPE . '/';
    
    /** @var IndividualExtSettings */
    protected $settings;
    
    public function __construct(
            IndividualExtSettings $settings)
    {
        $this->settings = $settings;
    }
    
    public function make(string $xref, Tree $tree, string $gedcom = null): ?Individual
    {
        $cache = Registry::cache()->array();
        
        return $cache->remember(__CLASS__ . $xref . '@' . $tree->id(), function () use ($xref, $tree, $gedcom) {
            $gedcom  = $gedcom ?? $this->gedcom($xref, $tree);
            $pending = $this->pendingChanges($tree)->get($xref);

            if ($gedcom === null && ($pending === null || !preg_match(self::TYPE_CHECK_REGEX, $pending))) {
                return null;
            }
            $xref = $this->extractXref($gedcom ?? $pending, $xref);

            return new IndividualExt($xref, $gedcom ?? '', $pending, $tree, $this->settings);
        });
    }
  
    public function new(string $xref, string $gedcom, ?string $pending, Tree $tree): Individual
    {
        return new IndividualExt($xref, $gedcom, $pending, $tree, $this->settings);
    }
}
