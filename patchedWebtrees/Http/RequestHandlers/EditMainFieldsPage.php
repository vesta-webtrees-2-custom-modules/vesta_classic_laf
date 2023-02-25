<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\GedcomEditService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function app;
use function route;

class EditMainFieldsPage implements RequestHandlerInterface {
    
    use ViewResponseTrait;

    private GedcomEditService $gedcom_edit_service;

    public function __construct() {
        $this->gedcom_edit_service = new GedcomEditServiceExt2(true);
        
        //explicitly register in order to re-use in views where we cannot pass via variable
        app()->instance(GedcomEditServiceExt2::class, new GedcomEditServiceExt2(true));
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $xref = Validator::attributes($request)->isXref()->string('xref');
        $record = Registry::gedcomRecordFactory()->make($xref, $tree);
        $record = Auth::checkRecordAccess($record, true);
        
        $names = array();
        $newFacts = array();
        $substrLength = 0;
        if ($record instanceof Individual) {
            $individual = $record;
            $sex = $individual->sex();
            $newFacts = $this->gedcom_edit_service->newIndividualFacts($tree, $sex, $names);
            //skip merging existingNames for now
            
            //strip off 'INDI:'
            $substrLength = 5;
        } else if ($record instanceof Family) {
            $family = $record;
            $newFacts = $this->gedcom_edit_service->newFamilyFacts($tree);
            
            //strip off 'FAM:'
            $substrLength = 4;
        } 
        
        $facts = array();
        $remainingFacts = array();
        
        foreach ($newFacts as $newFact) {
            $tag = substr($newFact->tag(),$substrLength); 
            //error_log("tag:".$tag);
            $existingFacts = $record->facts([$tag]);
            //error_log(print_r($existingFacts, true));
            
            if (count($existingFacts) > 0) {
                foreach ($existingFacts as $existingFact) {
                    $facts['fact-'.$existingFact->id().'-'] = [$existingFact];
                }
            } else {
                $remainingFacts []= $newFact;
            }
        }
        
        $facts['x'] = $remainingFacts;

        return $this->viewResponse('edit/existing-record', [
            'facts'               => $facts,
            'gedcom_edit_service' => $this->gedcom_edit_service,
            'post_url'            => route(EditMainFieldsAction::class, ['tree' => $tree->name(), 'xref' => $xref]),
            'title'               => $record->fullName(),
            'tree'                => $tree,
            'url'                 => Validator::queryParams($request)->isLocalUrl()->string('url', $record->url()),
        ]);
    }
}
