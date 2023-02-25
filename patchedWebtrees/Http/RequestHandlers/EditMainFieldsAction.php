<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\GedcomEditService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function redirect;

class EditMainFieldsAction implements RequestHandlerInterface {
    
    private GedcomEditService $gedcom_edit_service;

    public function __construct(GedcomEditService $gedcom_edit_service)
    {
        $this->gedcom_edit_service = $gedcom_edit_service;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree   = Validator::attributes($request)->tree();
        $xref   = Validator::attributes($request)->isXref()->string('xref');
        $record = Registry::gedcomRecordFactory()->make($xref, $tree);
        $record = Auth::checkRecordAccess($record, true);

        $keep_chan = Validator::parsedBody($request)->boolean('keep_chan', false);

        $fact_ids = array();
        
        $keys = array_keys((array)$request->getParsedBody());
        foreach ($keys as $key) {
            $parts = explode("-",$key);
            //fact-f65dc294a5d862a94b6a891b07db3d5f-levels
            if ((count($parts) === 3) && ($parts[0] === 'fact')) {
                $fact_ids[$parts[1]] = $parts[1];
            }
        }
        
        //error_log(print_r($keys, true));
        //error_log(print_r($fact_ids, true));
        
        //existing facts
        foreach ($fact_ids as $fact_id) {
            $levels = Validator::parsedBody($request)->array('fact-'.$fact_id.'-levels');
            $tags   = Validator::parsedBody($request)->array('fact-'.$fact_id.'-tags');
            $values = Validator::parsedBody($request)->array('fact-'.$fact_id.'-values');
            $gedcom = $this->gedcom_edit_service->editLinesToGedcom(Individual::RECORD_TYPE, $levels, $tags, $values);
            
            // Update (only the first copy of) an existing fact
            foreach ($record->facts([], false, null, true) as $fact) {
                if ($fact->id() === $fact_id && $fact->canEdit()) {
                    $record->updateFact($fact_id, $gedcom, !$keep_chan);
                    break;
                }
            }
        }
        
        //new facts        
        $levels = Validator::parsedBody($request)->array('xlevels');
        $tags   = Validator::parsedBody($request)->array('xtags');
        $values = Validator::parsedBody($request)->array('xvalues');
        
        if (count($levels) > 0) {
            $gedcom = $this->gedcom_edit_service->editLinesToGedcom(Individual::RECORD_TYPE, $levels, $tags, $values);
            $record->updateFact('', $gedcom, !$keep_chan);            
        }
        
        $url = Validator::parsedBody($request)->isLocalUrl()->string('url', $record->url());

        return redirect($url);
    }
}
