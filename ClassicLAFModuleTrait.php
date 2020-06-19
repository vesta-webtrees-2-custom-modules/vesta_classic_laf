<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckbox;
use Vesta\ControlPanelUtils\Model\ControlPanelPreferences;
use Vesta\ControlPanelUtils\Model\ControlPanelSection;
use Vesta\ControlPanelUtils\Model\ControlPanelSubsection;
use Vesta\ControlPanelUtils\Model\ControlPanelTextbox;

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
    
    $layout[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Overall width'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Use available space'),
                /* I18N: Module Configuration */I18N::translate('The standard layout centers most pages, wasting a lot of space especially on wide displays. This option allows to use most of the available space.'),
                'FULL_WIDTH', //TODO: css
                '1')));
    
    $layout[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Individual page'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Use compact layout'),
                /* I18N: Module Configuration */I18N::translate('Several adjustments - See Readme for details.'),
                'COMPACT_INDI_PAGE', //TODO: css
                '1')));
    
    $layout[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Edit dialogs'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Use compact layout'),
                /* I18N: Module Configuration */I18N::translate('Display all edit dialogs using a more compact layout, which also omits the standard header and footer.'),
                'COMPACT_EDIT',
                '1')));
    
    $general[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('XREF prefixes'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Custom prefixes'),
                /* I18N: Module Configuration */I18N::translate('Use custom prefixes for XREFs as in webtrees 1.x, instead of prefixing all XREFs with \'X\'.'),
                'CUSTOM_PREFIXES',
                '0'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Individual'),
                        null,
                        'GEDCOM_ID_PREFIX',
                        'I'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Family'),
                        null,
                        'FAM_ID_PREFIX',
                        'F'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Source'),
                        null,
                        'SOURCE_ID_PREFIX',
                        'S'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Repository'),
                        null,
                        'REPO_ID_PREFIX',
                        'R'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Media Object'),
                        null,
                        'MEDIA_ID_PREFIX',
                        'M'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Note'),
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
    
    //for now not configurable
    new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Layout'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Use compact layout for individual page'),
                /* I18N: Module Configuration */I18N::translate('...'),
                'COMPACT_INDI',
                '1')));
    
    $sections = array();
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('Layout'),
            '',
            $layout);
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */MoreI18N::xlate('General'),
            '',
            $general);
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */MoreI18N::xlate('Individuals'),
            '',
            $individuals);
    
    return new ControlPanelPreferences($sections);
  }
}
