<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Fisharebest\Webtrees\I18N;
use Vesta\ControlPanelUtils\Model\ControlPanelTextbox;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckbox;
use Vesta\ControlPanelUtils\Model\ControlPanelPreferences;
use Vesta\ControlPanelUtils\Model\ControlPanelSection;
use Vesta\ControlPanelUtils\Model\ControlPanelSubsection;

trait ClassicLAFModuleTrait {

  protected function getMainTitle() {
    return I18N::translate('Vesta Classic Look & Feel');
  }

  public function getShortDescription() {
    return I18N::translate('A module adjusting all themes and other features, providing a look & feel closer to the webtrees 1.x version.');
  }

  protected function getFullDescription() {
    $description = array();
    $description[] = 
            /* I18N: Module Configuration */I18N::translate('A module adjusting all themes and other features, providing a look & feel closer to the webtrees 1.x version.');
    $description[] = 
            /* I18N: Module Configuration */I18N::translate('Requires the \'%1$s Vesta Common\' module.', $this->getVestaSymbol());
    
    return $description;
  }

  protected function createPrefs() {
    $xrefs[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('XREF prefixes'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Custom prefixes'),
                /* I18N: Module Configuration */I18N::translate('Use custom prefixes for XREFs as in webtrees 1.x, instead of prefixing all XREFs with \'X\'.'),
                'CUSTOM_PREFIXES',
                '0'),
                new ControlPanelTextbox(
                        I18N::translate('Individual'),
                        null,
                        'GEDCOM_ID_PREFIX',
                        'I'),
                new ControlPanelTextbox(
                        I18N::translate('Family'),
                        null,
                        'FAM_ID_PREFIX',
                        'F'),
                new ControlPanelTextbox(
                        I18N::translate('Source'),
                        null,
                        'SOURCE_ID_PREFIX',
                        'S'),
                new ControlPanelTextbox(
                        I18N::translate('Repository'),
                        null,
                        'REPO_ID_PREFIX',
                        'R'),
                new ControlPanelTextbox(
                        I18N::translate('Media Object'),
                        null,
                        'MEDIA_ID_PREFIX',
                        'M'),
                new ControlPanelTextbox(
                        I18N::translate('Note'),
                        null,
                        'NOTE_ID_PREFIX',
                        'N')),
            /* I18N: Module Configuration */I18N::translate('In a family tree, each record has an internal reference number (called an "XREF") such as "F123" or "R14". You can choose the prefix that will be used whenever new XREFs are created.'));

    $individuals[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Nicknames'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Display nicknames before surnames'),
                /* I18N: Module Configuration */I18N::translate('Handle nicknames as in webtrees 1.x, i.e. show them before the surname.') . ' ' .
                /* I18N: Module Configuration */I18N::translate('Note that this doesn\'t affect GEDCOM name fields that already include a nickname, i.e. you may always position the nickname explicitly for specific names.'),
                'NICK_BEFORE_SURN',
                '1')));
    
    $sections = array();
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('General'),
            '',
            $xrefs);
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('Individuals'),
            '',
            $individuals);
    
    return new ControlPanelPreferences($sections);
  }
}
