<?php

declare(strict_types=1);

namespace Cissee\Webtrees\Module\ClassicLAF\SurnameTradition;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\SurnameTradition\SurnameTraditionInterface;

class SurnameTraditionWrapper implements SurnameTraditionInterface
{
    protected SurnameTraditionInterface $actual;

    public function __construct(
        SurnameTraditionInterface $actual) {

        $this->actual = $actual;
    }

    public function name(): string {
        return $this->actual->name();
    }

    public function description(): string {
        return $this->actual->description();
    }

    public function defaultName(): string {
        return $this->actual->defaultName();
    }

    public function newChildNames(?Individual $father, ?Individual $mother, string $sex): array {
        $names = $this->actual->newChildNames($father, $mother, $sex);
        return $this->adjust($names);
    }

    public function newParentNames(Individual $child, string $sex): array {
        $names = $this->actual->newParentNames($child, $sex);
        return $this->adjust($names);
    }

    public function newSpouseNames(Individual $spouse, string $sex): array {
        $names = $this->actual->newSpouseNames($spouse, $sex);
        return $this->adjust($names);
    }

    protected function adjust(array $names): array {
        if (sizeof($names) !== 1) {
            return $names;
        }

        //remove NameType::VALUE_BIRTH if there is only one name!
        $name = $names[0];
        $name = str_replace("\n2 TYPE BIRTH", "", $name);
        return [$name];
    }
}
