<?php

namespace Cissee\Webtrees\Module\ClassicLAF\WhatsNew;

use Cissee\WebtreesExt\WhatsNew\WhatsNewInterface;
use Fisharebest\Webtrees\I18N;

class WhatsNew1 implements WhatsNewInterface {
  
  public function getMessage(): string {
    return I18N::translate("Vesta Classic Look & Feel: Option to use custom prefixed xrefs, as in webtrees 1.x.");
  }
}
