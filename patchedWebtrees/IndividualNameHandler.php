<?php

namespace Cissee\WebtreesExt;

class IndividualNameHandler {
  
  protected $nickBeforeSurn = false;
          
  public function setNickBeforeSurn(bool $nickBeforeSurn) {
    $this->nickBeforeSurn = $nickBeforeSurn;
  }
  
  public function addNick(string $nameForDisplay, string $nick): string {
    if ($this->nickBeforeSurn) {
      //same logic as in webtrees 1.x
      $pos = strpos($nameForDisplay, '/');
			if ($pos === false) {
				// No surname - just append it
				return $nameForDisplay . ' "' . $nick . '"';
			} else {
				// Insert before surname
				return substr($nameForDisplay, 0, $pos) . '"' . $nick . '" ' . substr($nameForDisplay, $pos);
			}
    } else {
      //same logic as in original webtrees 2.x
      return $nameForDisplay . ' "' . $nick . '"';
    }
  }
}

