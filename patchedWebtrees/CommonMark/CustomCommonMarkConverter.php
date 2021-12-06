<?php

namespace Cissee\WebtreesExt\CommonMark;

use League\CommonMark\CommonMarkConverter;

class CustomCommonMarkConverter extends CommonMarkConverter {
  
  public function convertToHtml(string $commonMark): string {
    //[PATCHED]
    $commonMark = str_replace("\n","  \n", $commonMark);
    
    return parent::convertToHtml($commonMark);
  }
}
