<?php

namespace Cissee\WebtreesExt;

class IndividualExtSettings {

  protected $compactIndividualPage;
  protected $cropThumbnails;
  protected $expandFirstSidebar;

  public function compactIndividualPage(): bool {
    return $this->compactIndividualPage;
  }

  public function cropThumbnails(): bool {
    return $this->cropThumbnails;
  }

  public function expandFirstSidebar(): bool {
    return $this->expandFirstSidebar;
  }

  public function __construct(
      bool $compactIndividualPage,
      bool $cropThumbnails,
      bool $expandFirstSidebar)
  {
      $this->compactIndividualPage = $compactIndividualPage;
      $this->cropThumbnails = $cropThumbnails;
      $this->expandFirstSidebar = $expandFirstSidebar;
  }

}
