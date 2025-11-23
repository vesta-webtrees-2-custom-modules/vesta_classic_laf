<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Contracts\FamilyFactoryInterface;
use Fisharebest\Webtrees\Factories\FamilyFactory;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use function preg_match;

class CustomFamilyFactory extends FamilyFactory implements FamilyFactoryInterface
{
    private const TYPE_CHECK_REGEX = '/^0 @[^@]+@ ' . Family::RECORD_TYPE . '/';

    public function make(string $xref, Tree $tree, string $gedcom = null): ?Family
    {
        return Registry::cache()->array()->remember(__CLASS__ . $xref . '@' . $tree->id(), function () use ($xref, $tree, $gedcom) {
            $gedcom  = $gedcom ?? $this->gedcom($xref, $tree);
            $pending = $this->pendingChanges($tree)->get($xref);

            if ($gedcom === null && ($pending === null || !preg_match(self::TYPE_CHECK_REGEX, $pending))) {
                return null;
            }

            $xref = $this->extractXref($gedcom ?? $pending, $xref);

            //https://www.webtrees.net/index.php/forum/help-for-release-2-2-x/40264-stack-overflow-solved
            //note: removing this alone doesn't help wrt stack overflow/ memoty exhaustion
            //e.g. in descendany report in case of loops
            // Preload all the family members using a single database query.
            preg_match_all('/\n1 (?:HUSB|WIFE|CHIL) @(' . Gedcom::REGEX_XREF . ')@/', $gedcom . "\n" . $pending, $match);
            DB::table('individuals')
                ->where('i_file', '=', $tree->id())
                ->whereIn('i_id', $match[1])
                ->get()
                ->map(Registry::individualFactory()->mapper($tree));

            return new FamilyExt($xref, $gedcom ?? '', $pending, $tree);
        });
    }

    public function new(string $xref, string $gedcom, ?string $pending, Tree $tree): Family
    {
        return new FamilyExt($xref, $gedcom, $pending, $tree);
    }
}
