<?php

namespace Vesta\ControlPanel\Model;

class ControlPanelFactRestriction implements ControlPanelElement {

  private $family;
  private $description;
  private $settingKey;
  private $settingDefaultValue;

  public function getFamily() {
    return $this->family;
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
   * @param boolean $family
   * @param string|null $description
   * @param string $settingKey
   * @param string $settingDefaultValue
   */
  public function __construct($family, $description, $settingKey, $settingDefaultValue) {
    $this->family = $family;
    $this->description = $description;

    $this->settingKey = $settingKey;
    $this->settingDefaultValue = $settingDefaultValue;
  }

}
