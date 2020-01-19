<?php

namespace Vesta\ControlPanel\Model;

class ControlPanelSubsection {

  private $label;
  private $elements;

  /**
   * 
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @return ControlPanelElement[]
   */
  public function getElements() {
    return $this->elements;
  }

  public function __construct($label, $elements) {
    $this->label = $label;
    $this->elements = $elements;
  }

}
