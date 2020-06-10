<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\RequestHandlers\EditFact;

class EditFactAdjusted extends EditFact
{

  /** @var string */
  protected $layout = 'layouts/stripped';
}
