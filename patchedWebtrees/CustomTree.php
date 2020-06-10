<?php

namespace Cissee\WebtreesExt;

use Exception;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Factory;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Media;
use Fisharebest\Webtrees\Services\PendingChangesService;
use Fisharebest\Webtrees\Site;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use function app;

//[RC] restores webtrees xref handling, as suggested here:
//https://www.webtrees.net/index.php/en/forum/help-for-2-0/33978-identities-in-gedcom-file#74475
class CustomTree extends Tree {
  
  protected $module;
  
  public function __construct(int $id, string $name, string $title, $module) {
    parent::__construct($id, $name, $title);
    $this->module = $module;
  }
  
  public static function create(Tree $tree, $module = null): CustomTree {
    return new CustomTree($tree->id(), $tree->name(), $tree->title(), $module);
  }
  
  public function getNewXref(): string {
    return $this->getNewXrefByType(null);
  }
  
  /** @var string[] Which module preference is used for which record type */
  static $type_to_preference = array(
    'INDI' => 'GEDCOM_ID_PREFIX',
    'FAM'  => 'FAM_ID_PREFIX',
    'OBJE' => 'MEDIA_ID_PREFIX',
    'NOTE' => 'NOTE_ID_PREFIX',
    'SOUR' => 'SOURCE_ID_PREFIX',
    'REPO' => 'REPO_ID_PREFIX',
  );
    
  public function getNewXrefByType(?string $type): string
  {    
      // Lock the row, so that only one new XREF may be generated at a time.
      DB::table('site_setting')
          ->where('setting_name', '=', 'next_xref')
          ->lockForUpdate()
          ->get();

      //$prefix = 'X';
      
      //[RC] taken from webtrees 1.x and adjusted
      //Fallback: Use the first non-underscore character
      $prefix = substr(trim($type, '_'), 0, 1);
      //TODO: actually make configurable
      if (($type === null) || ($this->module === null)) {
        $prefix = 'X';
      } else if (array_key_exists($type, self::$type_to_preference)) {
        $prefix = $this->module->getPreference(self::$type_to_preference[$type], $prefix);
      }

      $increment = 1.0;
      do {
          $num = (int) Site::getPreference('next_xref') + (int) $increment;

          // This exponential increment allows us to scan over large blocks of
          // existing data in a reasonable time.
          $increment *= 1.01;

          $xref = $prefix . $num;

          // Records may already exist with this sequence number.
          $already_used =
              DB::table('individuals')->where('i_id', '=', $xref)->exists() ||
              DB::table('families')->where('f_id', '=', $xref)->exists() ||
              DB::table('sources')->where('s_id', '=', $xref)->exists() ||
              DB::table('media')->where('m_id', '=', $xref)->exists() ||
              DB::table('other')->where('o_id', '=', $xref)->exists() ||
              DB::table('change')->where('xref', '=', $xref)->exists();
      } while ($already_used);

      Site::setPreference('next_xref', (string) $num);

      return $xref;
  }
  
  //always returns GedcomRecord - not one of its subclasses!
  //despite doc on Tree.createRecord stating
  //@return GedcomRecord|Individual|Family|Location|Note|Source|Repository|Media|Submitter|Submission
  public function createRecord(string $gedcom): GedcomRecord
  {
      if (!Str::startsWith($gedcom, '0 @@ ')) {
          throw new InvalidArgumentException('GedcomRecord::createRecord(' . $gedcom . ') does not begin 0 @@');
      }

      //[RC] adjusted
      if (preg_match('/^0 @@ (' . Gedcom::REGEX_TAG . ')/', $gedcom, $match)) {
        $type = $match[1];
      } else {
        throw new Exception('Invalid argument to CustomTree::createRecord(' . $gedcom . ')');
      }
      
      $xref   = $this->getNewXrefByType($type);
      $gedcom = '0 @' . $xref . '@ ' . Str::after($gedcom, '0 @@ ');

      // Create a change record
      $today = strtoupper(date('d M Y'));
      $now   = date('H:i:s');
      $gedcom .= "\n1 CHAN\n2 DATE " . $today . "\n3 TIME " . $now . "\n2 _WT_USER " . Auth::user()->userName();

      // Create a pending change
      DB::table('change')->insert([
          'gedcom_id'  => $this->id(), //[RC] adjusted
          'xref'       => $xref,
          'old_gedcom' => '',
          'new_gedcom' => $gedcom,
          'user_id'    => Auth::id(),
      ]);

      // Accept this pending change
      if (Auth::user()->getPreference(User::PREF_AUTO_ACCEPT_EDITS)) {
          $record = Factory::gedcomRecord()->new($xref, $gedcom, null, $this);

          app(PendingChangesService::class)->acceptRecord($record);

          return $record;
      }

      return Factory::gedcomRecord()->new($xref, '', $gedcom, $this);
  }
  
  public function createFamily(string $gedcom): GedcomRecord
    {
        if (!Str::startsWith($gedcom, '0 @@ FAM')) {
            throw new InvalidArgumentException('CustomTree::createFamily(' . $gedcom . ') does not begin 0 @@ FAM');
        }

        //[RC] adjusted
        if (preg_match('/^0 @@ (' . Gedcom::REGEX_TAG . ')/', $gedcom, $match)) {
          $type = $match[1];
        } else {
          throw new Exception('Invalid argument to CustomTree::createRecord(' . $gedcom . ')');
        }

        $xref   = $this->getNewXrefByType($type);
        $gedcom = '0 @' . $xref . '@ ' . Str::after($gedcom, '0 @@ ');

        // Create a change record
        $today = strtoupper(date('d M Y'));
        $now   = date('H:i:s');
        $gedcom .= "\n1 CHAN\n2 DATE " . $today . "\n3 TIME " . $now . "\n2 _WT_USER " . Auth::user()->userName();

        // Create a pending change
        DB::table('change')->insert([
            'gedcom_id'  => $this->id(), //[RC] adjusted
            'xref'       => $xref,
            'old_gedcom' => '',
            'new_gedcom' => $gedcom,
            'user_id'    => Auth::id(),
        ]);

        // Accept this pending change
        if (Auth::user()->getPreference(User::PREF_AUTO_ACCEPT_EDITS)) {
            $record = Factory::family()->new($xref, $gedcom, null, $this);

            app(PendingChangesService::class)->acceptRecord($record);

            return $record;
        }

        return Factory::family()->new($xref, '', $gedcom, $this);
    }
    
  public function createIndividual(string $gedcom): GedcomRecord
    {
        if (!Str::startsWith($gedcom, '0 @@ INDI')) {
            throw new InvalidArgumentException('CustomTree::createIndividual(' . $gedcom . ') does not begin 0 @@ INDI');
        }
        
        //[RC] adjusted
        if (preg_match('/^0 @@ (' . Gedcom::REGEX_TAG . ')/', $gedcom, $match)) {
          $type = $match[1];
        } else {
          throw new Exception('Invalid argument to CustomTree::createRecord(' . $gedcom . ')');
        }

        $xref   = $this->getNewXrefByType($type);
        $gedcom = '0 @' . $xref . '@ ' . Str::after($gedcom, '0 @@ ');

        // Create a change record
        $today = strtoupper(date('d M Y'));
        $now   = date('H:i:s');
        $gedcom .= "\n1 CHAN\n2 DATE " . $today . "\n3 TIME " . $now . "\n2 _WT_USER " . Auth::user()->userName();

        // Create a pending change
        DB::table('change')->insert([
            'gedcom_id'  => $this->id(), //[RC] adjusted
            'xref'       => $xref,
            'old_gedcom' => '',
            'new_gedcom' => $gedcom,
            'user_id'    => Auth::id(),
        ]);

        // Accept this pending change
        if (Auth::user()->getPreference(User::PREF_AUTO_ACCEPT_EDITS)) {
            $record = Factory::individual()->new($xref, $gedcom, null, $this);

            app(PendingChangesService::class)->acceptRecord($record);

            return $record;
        }

        return Factory::individual()->new($xref, '', $gedcom, $this);
    }
  
  public function createMediaObject(string $gedcom): Media
    {
        if (!Str::startsWith($gedcom, '0 @@ OBJE')) {
            throw new InvalidArgumentException('CustomTree::createMediaObject(' . $gedcom . ') does not begin 0 @@ OBJE');
        }
        
        if (!Str::startsWith($gedcom, '0 @@ INDI')) {
            throw new InvalidArgumentException('CustomTree::createIndividual(' . $gedcom . ') does not begin 0 @@ INDI');
        }
        
        //[RC] adjusted
        if (preg_match('/^0 @@ (' . Gedcom::REGEX_TAG . ')/', $gedcom, $match)) {
          $type = $match[1];
        } else {
          throw new Exception('Invalid argument to CustomTree::createRecord(' . $gedcom . ')');
        }

        $xref   = $this->getNewXrefByType($type);
        $gedcom = '0 @' . $xref . '@ ' . Str::after($gedcom, '0 @@ ');

        // Create a change record
        $today = strtoupper(date('d M Y'));
        $now   = date('H:i:s');
        $gedcom .= "\n1 CHAN\n2 DATE " . $today . "\n3 TIME " . $now . "\n2 _WT_USER " . Auth::user()->userName();

        // Create a pending change
        DB::table('change')->insert([
            'gedcom_id'  => $this->id(), //[RC] adjusted
            'xref'       => $xref,
            'old_gedcom' => '',
            'new_gedcom' => $gedcom,
            'user_id'    => Auth::id(),
        ]);

        // Accept this pending change
        if (Auth::user()->getPreference(User::PREF_AUTO_ACCEPT_EDITS)) {
            $record = Factory::media()->new($xref, $gedcom, null, $this);

            app(PendingChangesService::class)->acceptRecord($record);

            return $record;
        }

        return Factory::media()->new($xref, '', $gedcom, $this);
    }
}
