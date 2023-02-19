<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
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
        $individual = Registry::individualFactory()->make($xref, $tree);
        $individual = Auth::checkIndividualAccess($individual, true);
        $sex = $individual->sex();
        
        $names = array();

        $newFacts = $this->gedcom_edit_service->newIndividualFacts($tree, $sex, $names);
        //skip merging existingNames for now
        
        $facts = array();
        $remainingFacts = array();
        
        foreach ($newFacts as $newFact) {
            $tag = substr($newFact->tag(),5); //strip off 'INDI:'
            //error_log("tag:".$tag);
            $existingFacts = $individual->facts([$tag]);
            //error_log(print_r($existingFacts, true));
            
            if (count($existingFacts) > 0) {
                foreach ($existingFacts as $existingFact) {
                    $facts['fact-'.$existingFact->id().'-'] = [$existingFact];
                }
            } else {
                $remainingFacts []= $newFact;
            }
        }
        
        $facts['i'] = $remainingFacts;

        return $this->viewResponse('edit/existing-individual', [
            'facts'               => $facts,
            'gedcom_edit_service' => $this->gedcom_edit_service,
            'post_url'            => route(EditMainFieldsAction::class, ['tree' => $tree->name(), 'xref' => $xref]),
            'title'               => $individual->fullName(),
            'tree'                => $tree,
            'url'                 => Validator::queryParams($request)->isLocalUrl()->string('url', $individual->url()),
        ]);
    }
}
