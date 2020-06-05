<?php

namespace Cissee\Webtrees\Module\ClassicLAF\WhatsNew;

use Cissee\WebtreesExt\WhatsNew\WhatsNewInterface;
use Fisharebest\Webtrees\I18N;

class WhatsNew0 implements WhatsNewInterface {
  
  public function getMessage(): string {
    return I18N::translate("Vesta Classic Look & Feel: Formerly known as Compact Themes Adjuster. Now part of the Vesta suite.");
  }
}
