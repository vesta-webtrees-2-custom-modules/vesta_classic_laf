<?php

namespace Vesta\ControlPanel\Model;

interface ControlPanelElement {

  /**
   * @return string|null
   */
  public function getDescription();

  /**
   * 
   * @return string
   */
  public function getSettingKey();
}
