<?php

namespace Cissee\WebtreesExt;

class FamilyNameHandler {
  
  protected $appendXref = false;

  public function setAppendXref(bool $appendXref) {
    $this->appendXref = $appendXref;
  }
  
  public function addXref(string $nameForDisplay, string $xref): string {
    if (!$this->appendXref) {
      return $nameForDisplay;
    }
    return $nameForDisplay . ' (' . $xref . ')';
  }
}

