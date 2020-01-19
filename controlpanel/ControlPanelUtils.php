<?php

namespace Vesta\ControlPanel;

use Cissee\WebtreesExt\ViewUtils;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\ControlPanel\Model\ControlPanelCheckbox;
use Vesta\ControlPanel\Model\ControlPanelCheckboxInverted;
use Vesta\ControlPanel\Model\ControlPanelElement;
use Vesta\ControlPanel\Model\ControlPanelFactRestriction;
use Vesta\ControlPanel\Model\ControlPanelPreferences;
use Vesta\ControlPanel\Model\ControlPanelRadioButtons;
use Vesta\ControlPanel\Model\ControlPanelRange;
use Vesta\ControlPanel\Model\ControlPanelSection;
use Vesta\ControlPanel\Model\ControlPanelSubsection;
use Exception;

class ControlPanelUtils {

  private $module;

  /**
   * 
   * @param ModuleInterface $module
   */
  public function __construct(ModuleInterface $module) {
    $this->module = $module;
  }

  /**
   * 
   * @return void
   */
  public function printPrefs(ControlPanelPreferences $prefs, $module) {
    ?>
    <h1><?php echo I18N::translate('Preferences'); ?></h1>

    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="route" value="module">
        <input type="hidden" name="module" value="<?php echo $module; ?>">
        <input type="hidden" name="action" value="Admin">
        <?php
        foreach ($prefs->getSections() as $section) {
          $this->printSection($section);
        }
        ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-check"></i>
                    <?php echo I18N::translate('save'); ?>
                </button>
            </div>
        </div>
    </form>
    <?php
  }

  /**
   * 
   * @return void
   */
  public function printSection(ControlPanelSection $section) {
    ?>
    <h3><?php echo $section->getLabel(); ?></h3>
    <?php
    $description = $section->getDescription();
    if ($description !== null) {
      ?>
      <p class="small text-muted">
          <?php echo $description; ?>
      </p>
      <?php
    }
    foreach ($section->getSubsections() as $subsection) {
      $this->printSubsection($subsection);
    }
  }

  /**
   * 
   * @return void
   */
  public function printSubsection(ControlPanelSubsection $subsection) {
    ?>
    <div class="row form-group">
        <label class="col-form-label col-sm-3">
            <?php echo $subsection->getLabel(); ?>
        </label>
        <div class="col-sm-9">
            <?php
            foreach ($subsection->getElements() as $element) {
              $this->printElement($element);
            }
            ?>
        </div>
    </div>
    <?php
  }

  public function printElement(ControlPanelElement $element) {
    if ($element instanceof ControlPanelCheckbox) {
      $this->printControlPanelCheckbox($element);
    } else if ($element instanceof ControlPanelCheckboxInverted) {
      $this->printControlPanelCheckboxInverted($element);
    } else if ($element instanceof ControlPanelFactRestriction) {
      $this->printControlPanelFactRestriction($element);
    } else if ($element instanceof ControlPanelRange) {
      $this->printControlPanelRange($element);
    } else if ($element instanceof ControlPanelRadioButtons) {
      $this->printControlPanelRadioButtons($element);
    } else {
      throw new Exception("unsupported ControlPanelElement");
    }

    $description = $element->getDescription();
    if ($description !== null) {
      ?>
      <p class="small text-muted">
          <?php echo $description; ?>
      </p>
      <?php
    }
  }

  public function printControlPanelCheckbox(ControlPanelCheckbox $element) {
    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());

    //ugly positioning of checkbox - for now, build checkbox directly (as in admin_trees_config)
    /*
      ?>
      <div class="optionbox">
      <?php echo ViewUtils::checkbox($element->getSettingKey(), $value, $element->getLabel()); ?>
      </div>
      <?php
     */
    ?>
    <div class="form-check">
        <label for="<?= $element->getSettingKey() ?>">
            <input name="<?= $element->getSettingKey() ?>" type="checkbox" id="<?= $element->getSettingKey() ?>" value="<?= $element->getSettingKey() ?>" <?= $value ? 'checked' : '' ?>>
            <?= $element->getLabel() ?>
        </label>
    </div>
    <?php
  }
  
  public function printControlPanelCheckboxInverted(ControlPanelCheckboxInverted $element) {
    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());

    //ugly positioning of checkbox - for now, build checkbox directly (as in admin_trees_config)
    /*
      ?>
      <div class="optionbox">
      <?php echo ViewUtils::checkbox($element->getSettingKey(), $value, $element->getLabel()); ?>
      </div>
      <?php
     */
    ?>
    <div class="form-check">
        <label for="<?= $element->getSettingKey() ?>">
            <input name="<?= $element->getSettingKey() ?>" type="checkbox" id="<?= $element->getSettingKey() ?>" value="<?= $element->getSettingKey() ?>" <?= $value ? '' : 'checked' ?>>
            <?= $element->getLabel() ?>
        </label>
    </div>
    <?php
  }
  
  public function printControlPanelFactRestriction(ControlPanelFactRestriction $element) {
    //why escape only here?	
    $value = e($this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue()));
    /*
    ?>
    <div class="col-sm-9">
        <?= Bootstrap4::multiSelect(
     GedcomTag::getPicklistFacts($element->getFamily() ? 'FAM' : 'INDI'), 
     explode(',', $value), 
     [
     'id' => $element->getSettingKey(), 
     'name' => $element->getSettingKey() . '[]', 
     'class' => 'select2']) ?>
    </div>
    <?php
    */
    echo view('components/select', [
        'name' => $element->getSettingKey() . '[]', 
        'id' => $element->getSettingKey(), 
        'selected' => explode(',', $value), 
        'options' => GedcomTag::getPicklistFacts($element->getFamily() ? 'FAM' : 'INDI'), 
        'class' => 'select2']);
  }

  public function printControlPanelRange(ControlPanelRange $element) {
    $value = (int)$this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());
    ?>
    <div class="input-group" style="min-width: 300px; max-width: 300px;">
        <label class="input-group-addon" for="<?php echo $element->getSettingKey(); ?>"><?php echo $element->getLabel() ?></label>
        <?php echo ViewUtils::select($element->getSettingKey(), array_combine(range($element->getMin(), $element->getMax()), range($element->getMin(), $element->getMax())), $value) ?>
    </div>
    <?php
  }

  public function printControlPanelRadioButtons(ControlPanelRadioButtons $element) {
    if ($element->getInline()) {
      $this->printControlPanelRadioButtonsInline($element);
      return;
    }

    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());
    foreach ($element->getValues() as $radioButton) {
      ?>
      <label>
          <input type="radio" name="<?php echo $element->getSettingKey(); ?>" value="<?php echo $radioButton->getValue(); ?>" <?php echo ($value === $radioButton->getValue()) ? 'checked' : ''; ?>>
          <?php echo $radioButton->getLabel(); ?>
      </label>
      <br>
      <?php
      $description = $radioButton->getDescription();
      if ($description !== null) {
        ?>
        <p class="small text-muted">
            <?php echo $description; ?>
        </p>
        <?php
      }
    }
  }

  public function printControlPanelRadioButtonsInline(ControlPanelRadioButtons $element) {
    $options = array();
    foreach ($element->getValues() as $value) {
      $options[$value->getValue()] = $value->getLabel();
      //note: description, if any, not displayed in inline mode!
    }

    $value = $this->module->getPreference($element->getSettingKey(), $element->getSettingDefaultValue());

    //problematic because array keys may be coverted to integer by php (even if explicitly set as string)
    //echo view('components/radios-inline', ['name' => $element->getSettingKey(), 'selected' => $value, 'options' => $options]);
    
    //fixed contents:
    foreach ($options as $optionValue => $label) {
      echo view('components/radio-inline', ['label' => $label, 'name' => $element->getSettingKey(), 'value' => (string)$optionValue, 'checked' => (string)$optionValue === $value]);
    }
  }

  /**
   * 
   * @return void
   */
  public function savePostData(ServerRequestInterface $request, ControlPanelPreferences $prefs) {
    foreach ($prefs->getSections() as $section) {
      foreach ($section->getSubsections() as $subsection) {
        foreach ($subsection->getElements() as $element) {
          if ($element instanceof ControlPanelFactRestriction) {
            $value = '';
            if (array_key_exists($element->getSettingKey(), $request->getParsedBody())) {
              $value = implode(',', $request->getParsedBody()[$element->getSettingKey()]);
            }
            $this->module->setPreference($element->getSettingKey(), $value);
          } else if ($element instanceof ControlPanelCheckbox) {
            $this->module->setPreference($element->getSettingKey(), (($request->getParsedBody()[$element->getSettingKey()] ?? null) != null)?'1':'0');
          } else if ($element instanceof ControlPanelCheckboxInverted) {
            $this->module->setPreference($element->getSettingKey(), (($request->getParsedBody()[$element->getSettingKey()] ?? null) != null)?'0':'1');
          } else {
            $this->module->setPreference($element->getSettingKey(), $request->getParsedBody()[$element->getSettingKey()]);
          }
        }
      }
    }

    FlashMessages::addMessage(I18N::translate('The preferences for the module “%s” have been updated.', $this->module->title()), 'success');
  }

}
