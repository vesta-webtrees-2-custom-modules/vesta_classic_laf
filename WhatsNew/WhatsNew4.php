<?php

namespace Cissee\Webtrees\Module\ClassicLAF\WhatsNew;

use Cissee\WebtreesExt\WhatsNew\WhatsNewInterface;

class WhatsNew4 implements WhatsNewInterface {

    public function getMessage(): string {
        //no I18N: I18N considered unnecessary, this is a one-off message.
        return "Vesta Classic Look & Feel: Option to configure name badges - See control panel for details.";
    }
}
