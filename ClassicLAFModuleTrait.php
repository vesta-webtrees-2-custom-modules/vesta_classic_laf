<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Cissee\WebtreesExt\MoreI18N;
use Fisharebest\Webtrees\I18N;
use Vesta\CommonI18N;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckbox;
use Vesta\ControlPanelUtils\Model\ControlPanelPreferences;
use Vesta\ControlPanelUtils\Model\ControlPanelSection;
use Vesta\ControlPanelUtils\Model\ControlPanelSubsection;
use Vesta\ControlPanelUtils\Model\ControlPanelTextbox;

trait ClassicLAFModuleTrait {

  protected function getMainTitle() {
    return CommonI18N::titleVestaCLAF();
  }

  public function getShortDescription() {
    return I18N::translate('A module adjusting all themes and other features, providing a look & feel closer to the webtrees 1.x version.');
  }

  protected function getFullDescription() {
    $description = array();
    $description[] = 
            /* I18N: Module Configuration */I18N::translate('A module adjusting all themes and other features, providing a look & feel closer to the webtrees 1.x version.');
    $description[] = 
            CommonI18N::requires1(CommonI18N::titleVestaCommon());
    
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
    
    $link = '<a href="https://github.com/vesta-webtrees-2-custom-modules/vesta_classic_laf">'.CommonI18N::readme().'</a>';
    
    $layout[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Individual page'),
            array(
                new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Use compact layout'),
                /* I18N: Module Configuration */I18N::translate('Several adjustments - See %1$s for details.', $link),
                'COMPACT_INDI_PAGE', //TODO: css
                '1'),
                new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Expand first sidebar'),
                /* I18N: Module Configuration */I18N::translate('Check to always expand the first sidebar, rather than the \'Family navigator\' sidebar.'),
                'EXPAND_FIRST_SIDEBAR',
                '0')));
    
    $layout[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Edit dialogs'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Use compact layout'),
                /* I18N: Module Configuration */I18N::translate('Display all edit dialogs using a more compact layout, which also omits the standard header and footer.'),
                'COMPACT_EDIT',
                '1')));
    
    $layout[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Image Thumbnails'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Crop Thumbnails'),
                /* I18N: Module Configuration */I18N::translate('Webtrees crops thumbnails in order to produce images with a consistent width and height. This is problematic if you have images of individals with a non-standard aspect ratio, where the head of the respective person is not centered and may therefore be cut off. Deselect this option to handle these cases.') . ' '.
                /* I18N: Module Configuration */I18N::translate('When switching this option, you will have to reset your thumbnail cache manually by deleting the folder %1$s.', '\'data/media/thumbnail-cache\''),
                'CROP_THUMBNAILS',
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
                        MoreI18N::xlate('Media object'),
                        null,
                        'MEDIA_ID_PREFIX',
                        'M'),
                new ControlPanelTextbox(
                        MoreI18N::xlate('Note'),
                        null,
                        'NOTE_ID_PREFIX',
                        'N'),
                
                //objects of this type may be handled via custom modules
                new ControlPanelTextbox(
                        MoreI18N::xlate('Location'),
                        null,
                        'LOCATION_ID_PREFIX',
                        'L')),
            
            /* I18N: Module Configuration */I18N::translate('In a family tree, each record has an internal reference number (called an "XREF") such as "F123" or "R14". You can choose the prefix that will be used whenever new XREFs are created.'));

    $individuals[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Nicknames'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Display nicknames before surnames'),
                /* I18N: Module Configuration */I18N::translate('Handle nicknames as in webtrees 1.x, i.e. show them before the surname.') . ' ' .
                /* I18N: Module Configuration */I18N::translate('Note that this doesn\'t affect GEDCOM name fields that already include a nickname, i.e. you may always position the nickname explicitly for specific names.'),
                'NICK_BEFORE_SURN',
                '1')));

    $individuals[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('XREFs'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Append XREFs to names'),
                /* I18N: Module Configuration */I18N::translate('Display an individual\'s XREF after the name.'),
                'APPEND_XREF',
                '0')));
    
    $families[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('XREFs'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Append XREFs to names'),
                /* I18N: Module Configuration */I18N::translate('Display a family\'s XREF after the name.'),
                'APPEND_XREF_FAM',
                '0')));
    
    //for now not configurable
    new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Layout'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */'Use compact layout for individual page',
                /* I18N: Module Configuration */'...',
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
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */MoreI18N::xlate('Families'),
            '',
            $families);
    
    return new ControlPanelPreferences($sections);
  }
}
