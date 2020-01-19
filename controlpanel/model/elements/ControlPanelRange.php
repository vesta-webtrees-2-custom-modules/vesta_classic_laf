<?php

namespace Vesta\ControlPanel\Model;

class ControlPanelRange implements ControlPanelElement {

  private $label;
  private $description;
  private $min;
  private $max;
  private $settingKey;
  private $settingDefaultValue;

  public function getLabel() {
    return $this->label;
  }

  public function getDescription() {
    return $this->description;
  }

  public function getMin() {
    return $this->min;
  }

  public function getMax() {
    return $this->max;
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
   * @param int $min
   * @param int $max
   * @param string $settingKey
   * @param int $settingDefaultValue
   */
  public function __construct($label, $description, $min, $max, $settingKey, $settingDefaultValue) {
    $this->label = $label;
    $this->description = $description;
    $this->min = $min;
    $this->max = $max;

    $this->settingKey = $settingKey;
    $this->settingDefaultValue = $settingDefaultValue;
  }

}
