<?php

declare(strict_types=1);

namespace Cissee\Webtrees\Module\ClassicLAF\Factories;

use Fisharebest\Webtrees\Factories\XrefFactory;

class CustomXrefFactory extends XrefFactory {

  protected $module;

  public function __construct($module) {
    $this->module = $module;
  }

  /** @var string[] Which module preference is used for which record type */
  static $type_to_preference = array(
    'INDI' => 'GEDCOM_ID_PREFIX',
    'FAM'  => 'FAM_ID_PREFIX',
    'OBJE' => 'MEDIA_ID_PREFIX',
    'NOTE' => 'NOTE_ID_PREFIX',
    'SOUR' => 'SOURCE_ID_PREFIX',
    'REPO' => 'REPO_ID_PREFIX',
    '_LOC' => 'LOCATION_ID_PREFIX',
  );

  public function make(string $record_type): string {
    //[RC] taken from webtrees 1.x and adjusted
    //Fallback: Use the first non-underscore character
    $prefix = substr(trim($record_type, '_'), 0, 1);
    if (($record_type === null) || ($this->module === null)) {
      $prefix = 'X';
    } else if (array_key_exists($record_type, self::$type_to_preference)) {
      $prefix = $this->module->getPreference(self::$type_to_preference[$record_type], $prefix);
    }

    return $this->generate($prefix, '');
  }
}
