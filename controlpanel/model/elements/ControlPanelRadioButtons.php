<?php

namespace Vesta\ControlPanel\Model;

class ControlPanelRadioButton {

  private $label;
  private $description;
  private $value;

  public function getLabel() {
    return $this->label;
  }

  public function getDescription() {
    return $this->description;
  }

  public function getValue() {
    return $this->value;
  }

  public function __construct($label, $description, $value) {
    $this->label = $label;
    $this->description = $description;
    $this->value = $value;
  }

}

class ControlPanelRadioButtons implements ControlPanelElement {

  private $inline;
  private $values;
  private $description;
  private $settingKey;
  private $settingDefaultValue;

  public function getInline() {
    return $this->inline;
  }

  public function getValues() {
    return $this->values;
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
   * @param boolean $inline
   * @param ControlPanelRadioButton[] $values
   * @param string|null $description
   * @param string $settingKey
   * @param string $settingDefaultValue
   */
  public function __construct($inline, $values, $description, $settingKey, $settingDefaultValue) {
    $this->inline = $inline;
    $this->values = $values;
    $this->description = $description;

    $this->settingKey = $settingKey;
    $this->settingDefaultValue = $settingDefaultValue;
  }

}
