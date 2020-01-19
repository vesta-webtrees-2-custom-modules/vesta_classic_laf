<?php

namespace Vesta\ControlPanel\Model;

class ControlPanelCheckboxInverted implements ControlPanelElement {

  private $label;
  private $description;
  private $settingKey;
  private $settingDefaultValue;

  public function getLabel() {
    return $this->label;
  }

  public function getDescription() {
    return $this->description;
  }

  public function getSettingKey() {
    return $this->settingKey;
  }

  public function getSettingDefaultValue() {
    return $this->settingDefaultValue;
  }

  /**
   * 
   * @param string $label
   * @param string|null $description
   * @param string $settingKey
   * @param string $settingDefaultValue
   */
  public function __construct($label, $description, $settingKey, $settingDefaultValue) {
    $this->label = $label;
    $this->description = $description;

    $this->settingKey = $settingKey;
    $this->settingDefaultValue = $settingDefaultValue;
  }

}
