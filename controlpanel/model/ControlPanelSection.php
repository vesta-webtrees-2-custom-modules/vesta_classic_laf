<?php

namespace Vesta\ControlPanel\Model;

class ControlPanelSection {

  private $label;
  private $description;
  private $subsections;

  /**
   * 
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * 
   * @return string|null
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @return ControlPanelSubsection[]
   */
  public function getSubsections() {
    return $this->subsections;
  }

  public function __construct($label, $description, $subsections) {
    $this->label = $label;
    $this->description = $description;
    $this->subsections = $subsections;
  }

}
