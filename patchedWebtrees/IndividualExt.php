<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Cissee\WebtreesExt\IndividualNameHandler;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use function app;

class IndividualExt extends Individual {
    
    /** @var IndividualExtSettings */
    protected $settings;
    
    public function settings(): IndividualExtSettings {
      return $this->settings;
    }
    
    public function __construct(
            string $xref, 
            string $gedcom, 
            ?string $pending, 
            Tree $tree, 
            IndividualExtSettings $settings)
    {
        parent::__construct($xref, $gedcom, $pending, $tree);
        
        $this->settings = $settings;
    }
  
    /**
     * Convert a name record into ‘full’ and ‘sort’ versions.
     * Use the NAME field to generate the ‘full’ version, as the
     * gedcom spec says that this is the individual’s name, as they would write it.
     * Use the SURN field to generate the sortable names. Note that this field
     * may also be used for the ‘true’ surname, perhaps spelt differently to that
     * recorded in the NAME field. e.g.
     *
     * 1 NAME Robert /de Gliderow/
     * 2 GIVN Robert
     * 2 SPFX de
     * 2 SURN CLITHEROW
     * 2 NICK The Bald
     *
     * full=>'Robert de Gliderow 'The Bald''
     * sort=>'CLITHEROW, ROBERT'
     *
     * Handle multiple surnames, either as;
     *
     * 1 NAME Carlos /Vasquez/ y /Sante/
     * or
     * 1 NAME Carlos /Vasquez y Sante/
     * 2 GIVN Carlos
     * 2 SURN Vasquez,Sante
     *
     * @param string $type
     * @param string $full
     * @param string $gedcom
     *
     * @return void
     */
    protected function addName(string $type, string $full, string $gedcom): void
    {
        ////////////////////////////////////////////////////////////////////////////
        // Extract the structured name parts - use for "sortable" names and indexes
        ////////////////////////////////////////////////////////////////////////////

        $sublevel = 1 + (int) substr($gedcom, 0, 1);
        $GIVN     = preg_match("/\n{$sublevel} GIVN (.+)/", $gedcom, $match) ? $match[1] : '';
        $SURN     = preg_match("/\n{$sublevel} SURN (.+)/", $gedcom, $match) ? $match[1] : '';
        $NICK     = preg_match("/\n{$sublevel} NICK (.+)/", $gedcom, $match) ? $match[1] : '';

        // SURN is an comma-separated list of surnames...
        if ($SURN !== '') {
            $SURNS = preg_split('/ *, */', $SURN);
        } else {
            $SURNS = [];
        }

        // ...so is GIVN - but nobody uses it like that
        $GIVN = str_replace('/ *, */', ' ', $GIVN);

        ////////////////////////////////////////////////////////////////////////////
        // Extract the components from NAME - use for the "full" names
        ////////////////////////////////////////////////////////////////////////////

        // Fix bad slashes. e.g. 'John/Smith' => 'John/Smith/'
        if (substr_count($full, '/') % 2 === 1) {
            $full .= '/';
        }

        // GEDCOM uses "//" to indicate an unknown surname
        $full = preg_replace('/\/\//', '/@N.N./', $full);

        // Extract the surname.
        // Note, there may be multiple surnames, e.g. Jean /Vasquez/ y /Cortes/
        if (preg_match('/\/.*\//', $full, $match)) {
            $surname = str_replace('/', '', $match[0]);
        } else {
            $surname = '';
        }

        // If we don’t have a SURN record, extract it from the NAME
        if (!$SURNS) {
            if (preg_match_all('/\/([^\/]*)\//', $full, $matches)) {
                // There can be many surnames, each wrapped with '/'
                $SURNS = $matches[1];
                foreach ($SURNS as $n => $SURN) {
                    // Remove surname prefixes, such as "van de ", "d'" and "'t " (lower case only)
                    $SURNS[$n] = preg_replace('/^(?:[a-z]+ |[a-z]+\' ?|\'[a-z]+ )+/', '', $SURN);
                }
            } else {
                // It is valid not to have a surname at all
                $SURNS = [''];
            }
        }

        // If we don’t have a GIVN record, extract it from the NAME
        if (!$GIVN) {
            $GIVN = preg_replace(
                [
                    '/ ?\/.*\/ ?/',
                    // remove surname
                    '/ ?".+"/',
                    // remove nickname
                    '/ {2,}/',
                    // multiple spaces, caused by the above
                    '/^ | $/',
                    // leading/trailing spaces, caused by the above
                ],
                [
                    ' ',
                    ' ',
                    ' ',
                    '',
                ],
                $full
            );
        }

        // Add placeholder for unknown given name
        if (!$GIVN) {
            $GIVN = self::PRAENOMEN_NESCIO;
            $pos  = (int) strpos($full, '/');
            $full = substr($full, 0, $pos) . '@P.N. ' . substr($full, $pos);
        }

        //[RC] adjusted
        $fullForFullNN = $full;

        //Issue #115
        //slashes in $full have special meaning, must use something else (at least for creating the html)
        //should we revert this for display afterwards?
        // not sure, if nickname is set as part of full by user, he wouldn't be able to use a slash either
        //(anyway there is nothing in the spec about this case)
        $NICK = str_replace('/', '|', $NICK);
        
        //[RC] adjusted: logic is configurable        
        if ($NICK && strpos($full, '"' . $NICK . '"') === false) {
            // A NICK field is present, but not included in the NAME.
            // we may have to handle it specifically            
            $handler = app(IndividualNameHandler::class);
            $full = $handler->addNick($full, $NICK);
        }

        //moved to fullName(): we don't want this e.g. when using individual name for family name
        //$full = $handler->addXref($full, $this->xref());
        
        // Remove slashes - they don’t get displayed
        // $fullNN keeps the @N.N. placeholders, for the database
        // $full is for display on-screen
        $fullNN = str_replace('/', '', $fullForFullNN);

        // Insert placeholders for any missing/unknown names
        $full = str_replace(self::NOMEN_NESCIO, I18N::translateContext('Unknown surname', '…'), $full);
        $full = str_replace(self::PRAENOMEN_NESCIO, I18N::translateContext('Unknown given name', '…'), $full);
        // Format for display
        $full = '<span class="NAME" dir="auto" translate="no">' . preg_replace('/\/([^\/]*)\//', '<span class="SURN">$1</span>', e($full)) . '</span>';
        // Localise quotation marks around the nickname
        $full = preg_replace_callback('/&quot;([^&]*)&quot;/', static function (array $matches): string {
            return '<q class="wt-nickname">' . $matches[1] . '</q>';
        }, $full);

        // A suffix of “*” indicates a preferred name
        //[RC] this was originally adjusted, rationale: '-' is a breaking-space character, therefore break preferred name as well (this is to handle names with hyphens where only the last part is the preferred name)
        //(although note that 'official' german rufname must always be full hyphenated name)
        //explicitly use unicode 2011 'Non-Breaking Hyphen' if intended otherwise
        //
        //note: webtrees has different solution for this since fix for https://github.com/fisharebest/webtrees/issues/1010
        //Issue #76: we'll just align with that, everything else gets too complicated
        //(we would have to add config option whether to break on breaking-space characters)
        //anyway the more common case seems to be the one where you want all parts as preferred name
        $full = preg_replace('/([^ >\x{200C}]*)\*/u', '<span class="starredname">\\1</span>', $full);
        
        // Remove prefered-name indicater - they don’t go in the database
        $GIVN   = str_replace('*', '', $GIVN);
        $fullNN = str_replace('*', '', $fullNN);

        foreach ($SURNS as $SURN) {
            // Scottish 'Mc and Mac ' prefixes both sort under 'Mac'
            if (strcasecmp(substr($SURN, 0, 2), 'Mc') === 0) {
                $SURN = substr_replace($SURN, 'Mac', 0, 2);
            } elseif (strcasecmp(substr($SURN, 0, 4), 'Mac ') === 0) {
                $SURN = substr_replace($SURN, 'Mac', 0, 4);
            }

            $this->getAllNames[] = [
                'type'    => $type,
                'sort'    => $SURN . ',' . $GIVN,
                'full'    => $full,
                // This is used for display
                'fullNN'  => $fullNN,
                // This goes into the database
                'surname' => $surname,
                // This goes into the database
                'givn'    => $GIVN,
                // This goes into the database
                'surn'    => $SURN,
                // This goes into the database
            ];
        }
    }
    
    public function fullName(): string
    {
        //[RC] adjusted: logic is configurable
        $handler = app(IndividualNameHandler::class);
        
        $full = parent::fullName();
        $full .= $handler->addBadges($this->tree(), $this->gedcom);
        $full = $handler->addXref($full, $this->xref());
        return $full;
    }
}
