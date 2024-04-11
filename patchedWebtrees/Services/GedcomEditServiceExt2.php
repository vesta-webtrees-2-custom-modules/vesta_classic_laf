<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Services;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\GedcomEditService;
use Fisharebest\Webtrees\Tree;
use ReflectionMethod;
use const PHP_INT_MAX;
use function array_shift;
use function explode;
use function implode;
use function max;
use function preg_split;
use function str_ends_with;
use function str_repeat;
use function str_starts_with;
use function substr_count;

//renamed due to overlap with obsolete file from 'Shared Places'
//(safer this way in case the obsolete file isn't removed)
class GedcomEditServiceExt2 extends GedcomEditService
{
    protected bool $forNewIndividual;
    protected string $vesta_symbol;
    protected string $house_symbol;

    public function __construct(
        bool $forNewIndividual = false) {

        $this->forNewIndividual = $forNewIndividual;
        $this->vesta_symbol = json_decode('"\u26B6"');
        $this->house_symbol = json_decode('"\u2302"');
    }

    protected function insertMissingLevels(Tree $tree, string $tag, string $gedcom, bool $include_hidden): string
    {
        $next_level = substr_count($tag, ':') + 1;
        $factory    = Registry::elementFactory();
        $subtags    = $factory->make($tag)->subtags();

        // The first part is level N.  The remainder are level N+1.
        $parts  = preg_split('/\n(?=' . $next_level . ')/', $gedcom);
        $return = array_shift($parts) ?? '';

        foreach ($subtags as $subtag => $occurrences) {
            if (!$include_hidden) {

                $hidden = $this->isHiddenTagExt(
                    $tree,
                    $tag . ':' . $subtag,
                    str_ends_with($occurrences, ':?'));

                if ($hidden) {

                    //we want to preserve overall order in any case
                    //(in particular in the 'edit main facts and events' dialog)
                    //therefore show existing hidden at its 'normal' position, rather than at the end
                    $existing = false;
                    foreach ($parts as $n => $part) {
                        if (str_starts_with($part, $next_level . ' ' . $subtag)) {
                            $existing = true;
                            break;
                        }
                    }

                    if (!$existing) {
                        continue;
                    }
                }
            }

            [$min, $max] = explode(':', $occurrences);

            $min = (int) $min;

            if ($max === 'M') {
                $max = PHP_INT_MAX;
            } else {
                $max = (int) $max;
            }

            $count = 0;

            // Add expected subtags in our preferred order.
            foreach ($parts as $n => $part) {
                if (str_starts_with($part, $next_level . ' ' . $subtag)) {
                    $return .= "\n" . $this->insertMissingLevels($tree, $tag . ':' . $subtag, $part, $include_hidden);
                    $count++;
                    unset($parts[$n]);
                }
            }

            // Allowed to have more of this subtag?
            if ($count < $max) {
                // Create a new one.
                $gedcom  = $next_level . ' ' . $subtag;
                $default = $factory->make($tag . ':' . $subtag)->default($tree);
                if ($default !== '') {
                    $gedcom .= ' ' . $default;
                }

                $number_to_add = max(1, $min - $count);
                $gedcom_to_add = "\n" . $this->insertMissingLevels($tree, $tag . ':' . $subtag, $gedcom, $include_hidden);

                $return .= str_repeat($gedcom_to_add, $number_to_add);
            }
        }

        // Now add any unexpected/existing data.
        if ($parts !== []) {
            $return .= "\n" . implode("\n", $parts);
        }

        return $return;
    }

    public function getPreference(
        Tree $tree,
        string $tag): string {

        $tag = $this->compressTag($tag);
        $pref = $this->vesta_symbol . $this->house_symbol . $tag;
        $override = $tree->getPreference($pref);
        return $override;
    }

    public function setPreference(
        Tree $tree,
        string $tag,
        string $value) {

        $tag = $this->compressTag($tag);
        $pref = $this->vesta_symbol . $this->house_symbol . $tag;
        //TODO would be better to delete pref in case of empty string
        $tree->setPreference($pref, $value);
    }

    protected function isHiddenTagExt(
        Tree $tree,
        string $tag,
        bool $hiddenViaOccurrences): bool {

        $r = new ReflectionMethod(parent::class, 'isHiddenTag');
        $r->setAccessible(true);
        $originalRet = $hiddenViaOccurrences || $r->invokeArgs($this, [$tag]);

        //error_log("hidden? " . $tag . '=' . $originalRet);

        //check for specific override
        $tag = $this->compressTag($tag);
        $pref = $this->vesta_symbol . $this->house_symbol . $tag;

        //error_log("override? " . $pref);

        $override = $tree->getPreference($pref);
        if ($override !== '') {
            switch ($override) {
                //hide always
                case 'LEVEL0':
                    $overrideRet = true;
                    break;
                //hide for new individual, show otherwise
                case 'LEVEL1':
                case 'LEVEL1a':
                    if ($this->forNewIndividual) {
                        $overrideRet = true;
                    } else {
                        $overrideRet = false;
                    }
                    break;
                //show always
                case 'LEVEL2':
                case 'LEVEL2a':
                    $overrideRet = false;
                    break;
                //(unexpected; hide always)
                default:
                    $overrideRet = true;
                    break;
            }

            if ($originalRet !== $overrideRet) {
                //error_log($tag . "override specific to ".$overrideRet);
            }

            return $overrideRet;
        }

        //check for generic override
        $tag2parts = explode(':',$tag);
        $tag2parts[1] = '*';
        $tag2 = implode(':',$tag2parts);

        $tag2 = $this->compressTag($tag2);
        $pref = $this->vesta_symbol . $this->house_symbol . $tag2;

        //error_log("override? " . $pref);

        $override = $tree->getPreference($pref);
        if ($override !== '') {
            switch ($override) {
                //hide always
                case 'LEVEL0':
                    $overrideRet = true;
                    break;
                //hide for new individual, show otherwise
                case 'LEVEL1':
                case 'LEVEL1a':
                    if ($this->forNewIndividual) {
                        $overrideRet = true;
                    } else {
                        $overrideRet = false;
                    }
                    break;
                //show always
                case 'LEVEL2':
                case 'LEVEL2a':
                    $overrideRet = false;
                    break;
                //(unexpected; hide always)
                default:
                    $overrideRet = true;
                    break;
            }

            if ($originalRet !== $overrideRet) {
                //error_log($tag2 . " override generic to ".$overrideRet);
            }

            return $overrideRet;
        }

        return $originalRet;
    }

    public function alwaysExpandSubtags(
        Tree $tree,
        string $tag): bool {

        //check for specific override
        $tag = $this->compressTag($tag);
        $pref = $this->vesta_symbol . $this->house_symbol . $tag;

        //error_log("override? " . $pref);

        $override = $tree->getPreference($pref);
        if ($override !== '') {
            switch ($override) {
                case 'LEVEL1a':
                case 'LEVEL2a':
                    return true;
                default:
                    break;
            }
        }

        //check for generic override
        $tag2parts = explode(':',$tag);
        $tag2parts[1] = '*';
        $tag2 = implode(':',$tag2parts);

        $tag2 = $this->compressTag($tag2);
        $pref = $this->vesta_symbol . $this->house_symbol . $tag2;

        //error_log("override? " . $pref);

        $override = $tree->getPreference($pref);
        if ($override !== '') {
            switch ($override) {
                case 'LEVEL1a':
                case 'LEVEL2a':
                    return true;
                default:
                    break;
            }
        }

        return false;
    }

    protected function compressTag(string $tag): string {
        //we only have 32 chars in table column!
        //(first 2 of those are used for pref outside tag, i.e. 30 left
        //note that this may still be problematic for custom tags)
        $tagCompressed = $tag;
        $tagCompressed = str_replace("INDI","I", $tagCompressed);
        $tagCompressed = str_replace("FAM","F", $tagCompressed);
        $tagCompressed = str_replace("ASSO","A", $tagCompressed);
        $tagCompressed = str_replace("SOUR","S", $tagCompressed);
        $tagCompressed = str_replace("PLAC","P", $tagCompressed);

        return $tagCompressed;
    }
}
