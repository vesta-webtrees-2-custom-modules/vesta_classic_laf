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
        //[RC] adjusted, rationale: '-' is a breaking-space character, therefore break preferred name as well (this is to handle names with hyphens where only the last part is the preferred name)
        //(although note that 'official' german rufname must always be full hyphenated name)
        //explicitly use unicode 2011 'Non-Breaking Hyphen' if intended otherwise
        $full = preg_replace('/([^ ->]*)\*/', '<span class="starredname">\\1</span>', $full);

        //'stickers'
        /*
        //CHR with SOUR (before next fact starts)
				if (preg_match('/\n1 CHR(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $this->gedcom, $match)) {
					//img source: "webtrees\resources\css\facts\CHR.png"
					$full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAD30lEQVR4AWOgBRAMr2A0XvuMiSaGGyRWMmqUzmKSr13FJF65kqAlEgWzGFEEHv6njsMsSiYy4pLjbdnHiE8vX95sMY28fp+g8k4LmBhb4RJG6jgsIEZGs3SWu1Ldaj/58kX6+NRKpXSALWWxCFRUb1y1X6Nr+0nJjN7o9K4ZIhQ7xL2wXdewam69WtG0icrFM6eLV65YxlmzZSNz3c4exrL15vj0NkS6Sqg0rTvB27z7rVTNqtt2LUvL/8cwkB91fgX15toNK89r1K94pF6zZIFi5eJG0Yrl7bxNO/dyth74wtS49xpX/fZykdxpKuh6/zMwMJq1rJgr37jhtVzL1kOG7RvO6iTVZAKlBICYEYoJA6cJ28AKo7KKNDXqVpznrd7wi79m/TqZ7gOzJDv2rJCo37BUumbVdLGaNfO4G7bvE6jffJq3acc+7rYD3UKlSxxNPYLULAp7Q3UbV65RbFh7X7Bh6yaV1i3n3FoX95gxMMgDjVZmYmGVBdJcBB1jHRQLdsw1BgYmo7Lpc+Ratt0Va983W6XvwG7ZvsO3RToP3pJo3npYsm7dGsXG9fOkatfO5C9btpC7dvMp7rotN3lqN52QqFmzU7J61Q3JmtUPZBo3LJbsOrjDqG3dRefEfH+g0ZKMnDzaQFoRiDmJjirb0okhGnXL70h17puvP/3kDs2WdQ+NcrumayfV5ornTmvkr9tyULhl9xOxxi1XgY7bL1m2eIVo0dyNImVLDorVrtsr1Lp7t2jP0Z0yk04eVe/c8dyxenqHNguDMtBoFVY2NlUgLUycS3b9Z3Tw8JQwrZw1Q7lj51HpnkPLdFvW3bAvaC/VBWYeqEEKnGbeNmxJfXU8ZSv2KzauPavYsOaoZOXyrdKlC1aJVSzbIVy7/pxU686Hik0bHpgXdE90VJXUBOoTY2bjADlGDpT7iQ0cVp2K2eVKLZveCnccOKnZuf2aW930tlAGBlEGCJaHGsgLxDwixk5GAqk95VylK7byVa3bJd24abVy+7adKg1rTxhXzFzjFpcZrMTAIM0AwSqMjAygxC9IlEvU/v8Hpx/V6qXWgm1713E27rqv3rT2oUFCeQzYMcysqoyMjCrgXIIK2MT4eaQNbF2MDb3CXK0CY9xDwqNM/JX5QSEqBMSgBKwF1KsB8RSZ2V6iYkm5QvmC6coBqYGccup6oKgCWYAvdKG+lwRicRCG6lGBOooPnNXJBesXz+YKMFUXURDgUOIXFNIH5QxCcQ+1kAMapfxQzAPELAxUBNxALAMMcnGogwYeAB3DCo0SJoYhAUbBKBgFo2AUjIJRMApGwSgYBQDOiTUkxyOs/QAAAABJRU5ErkJggg==" />';
				}
				
				//DEAT with SOUR (before next fact starts)
				if (preg_match('/\n1 DEAT(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $this->gedcom, $match)) {
					//img source: "webtrees\resources\css\facts\DEAT.png"
					$full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAFdElEQVR4Ac2WW4hdVxnH/+tba6+197mcyVzSmTEmE2uoEVv7YFCskrEPihAvhSrog0IRWlGxDaZFfaigD4KCF5/EGmwV9UHEFhUfBGuxpemgJOroxCRMLiGdyVzOydnn7Mve6/IZyMTnZpIe83vebH78+a/v+3C7IXADfObhD4j59+0VB+/v8M+e+TMAMG5jRp/Qw5/92N5mq75/MFiJne++BOAf/zehRx87MhGZxacFZR/23g28L056774I4BhuIQqvEaXP3Bsn/oNF0d9UUXyRZPR2LhtfWL30pmUAayMXqu3gbYmwMgR93ii9SYImh311lzb9BxYXF/8A4OIIhVho9f45a3MBERLv7KyHHA9BrQmRUK9X7zh/7tUrAAa4SQiviQNkbVnryORJovZ5b3cNh3ncbMX9QRpOXJXpAYgAiNcloYc+/WQyt+9ke+GV00MAORC8Z74AyI2iyGB04omcKnK38OyzL28AaAIoADC2OPzEAQIQtv3K3nuwEyXJ3RNv3teaj6JwkAQS59OiqosTzuLfU1N75iKdH86LVE6M7ywG6XBvd5N+0evaCyap3tpsihTCnmSm87bKjwPoYxuIRw9/VADgV15ear7r3fd8rtnKvlrVPXKOT5mYZznAhxCuEMVDhp/x3rV01LkcQjZZVcFJ2SikhCEqdpKkhrVelEX1veHwwSMA/E3Noce/fOhbKqoeqevhQmBWwftUUsSCsNcY/xZmL60VK0JQXlunh8NqU6t4NYoaXaIgSLpZkuGu4ClxtvkEgGd++7s/CQC8rQ5l2caViSnt6sxtStEUxvAewCcA4qqiQke6krIiIkwIoYM3ZkNFfjdRMWNM47IxRkO4bDjwc1lW33f0Ry8+h2tc2ZZQVZfH0n5+TJKZM1qPhcDWWn+hqqs0G/C/Io1dcSwfNHGwRKQYaq2u2PlQv8H7cr/3esp7jxCa/yxy8TcAEwDEVQoA1Q0LPf/83/8yP38PT0503jNMubO+XqVAa6Xdnln76dPHXzr0EfOJ/fvvOKQiu5FlLjq3XP7m1FJ2fHqWdt8xHXY411PeiVrKxuqJ4xcvAUgAIZg53pbQ8inYhb9+7YWjT/1x4RtP/tp0dvhd0zPtMWOkA85OdjrvvNMktlHk1jvrRbut6fSZ/3RPn+mnQKsHDB0ADSw2ADSJSIYQcgB2e6X+yvz/Cvjtb74gAbQASADVxPjdY5/81NTX253i4xBypczhizz5yS9/vvRcmr7qVITNmdk4DFJnvJOyrhl1XXsAJYAcAN+KbU9b3/gjjz805/nS933oTmqtVoWQB7Kh+dWPf7j0VG1XSgCrAOzrvTrC9VmSNGRMhD3e2ygE3yURiEhMeqcJAG0liRHtMqCfrr4x0qyI0CchUgbVJBQpKa93UY1UiLlqSBIVUWPdB104C6uUHhA1SwA0ciHnuBJECQdvBMJ4YNGSSveLOs62/kMjPdCECNPMLvLBNRFCKzhJinwBaItr8EgTkpJbIYRSSn1JySgVAo45ADBiq/hhpAkxi6C1aleVmFKKDSCa1joDrBsAPHIh5+q+czxjre0CClKGHXVVdgSs5mtCGG2prV0tC99rNJrrYHWWKLqslGp32r6Ba4iRCimVFGXJqQ9lYM4LZuo558aTRjYGQIx8MHbGGhwncgzspiOD2MQUS4mdrXakt/pDIxUqS7EBrnpChN3gZkeSmpBSkpQNC8CPXOgH3/39cl4MvmNr9KuS7037LFwdv7i+ZtcAxACikb2yx7503/Wz5KiUdJpZveP82TycXFo71u2dCQAMgCFuAQLbpwVg11Yyfuv86OEmkbhBHvn8nfShByTOLRtZFsFfpQTQB5ACYNwGCNxC/gta/Lpwww0OLQAAAABJRU5ErkJggg==" />';
				}
				
				//BURI with SOUR (before next fact starts)
				if (preg_match('/\n1 BURI(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $this->gedcom, $match)) {
					//img source: "webtrees\resources\css\facts\BURI.png"
					$full .= '<img width="16" src="data:image/png;base64,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" />';
				}
        
				if (preg_match('/\n1 _FSFTID/', $this->gedcom, $match)) {
          $full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAe1BMVEUAAAC6t7GFuUCFuUC6t7G6t7GblHqblHqFuUCblHqblHqblHqblHqblHqFuUCblHqblHqblHqFuUC6t7GblHq6t7G6t7GmoI26t7G6t7G2sqmuqpyNq1aFuUCFuUCHtUa6t7GFuUCblHq6t7G6t7GblHqFuUCrppaQp127S6TeAAAAJHRSTlMAgIBAQL9Av78wz4Bg7+/fjyAQz3BgIBDv36fPz8+fj3BwUDC6gAAMAAABTklEQVRIx7XT23KDIBCA4UVEA5JEc056brNp3/8Ju2wcbKoSmDH/td8AC8IUrWTbKhLIS5uMBosZtUgAM6Bmk4Od4LbRID9zKgEchVAMukOHgQIQDK7Fgr8XJ3z5EDgodXSq6+wTgUPrsk2HwT7nvqDAtoLAOqfeGYxFwBSUYaB48R6QmU8SKIEqQyC7+LKHgQ/pInB7hoOi1kNAgusKuin5QkAvEQ3fQySAJc730GscWMRqJ3xbyLhxQDtq8rNPwYUbHatGNHALXqV8GQc1Yt1NXjmQuf9v6Gm8/Txn0iB+3gFdJVpARP0PcINAI5YE4BZ0U+pnEfsgmPXglLvW9wDULfDdAxWBJgVs+Jnunr5P/mmEKwhUYPn9RaUJmBLRQmzIWYjO3dw84Xs+tYGEGuQ5QdqelimgQKpKXQKL1CXmOkHUycKkiqZybWCCfgFH7T9amDw6vQAAAABJRU5ErkJggg==" />';
        }
        */
        
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
        $full = $handler->addXref($full, $this->xref());
        return $full;
    }
}
