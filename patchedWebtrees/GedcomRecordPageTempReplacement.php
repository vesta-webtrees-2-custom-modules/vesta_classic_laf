<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\ClipboardService;
use Fisharebest\Webtrees\Services\LinkedRecordService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function redirect;

/**
 * Display non-standard genealogy records.
 */
class GedcomRecordPageTempReplacement implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ClipboardService $clipboard_service;

    private LinkedRecordService $linked_record_service;

    /**
     * @param ClipboardService $clipboard_service
     * @param LinkedRecordService $linked_record_service
     */
    public function __construct(ClipboardService $clipboard_service, LinkedRecordService $linked_record_service)
    {
        $this->clipboard_service     = $clipboard_service;
        $this->linked_record_service = $linked_record_service;
    }

    /**
     * Show a gedcom record's page.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree   = Validator::attributes($request)->tree();
        $xref   = Validator::attributes($request)->isXref()->string('xref');
        $record = Registry::gedcomRecordFactory()->make($xref, $tree);
        $record = Auth::checkRecordAccess($record);
        
        // Standard genealogy records have their own pages.
        $genericRecord = Registry::gedcomRecordFactory()->new($xref, '', null, $tree);
        if ($record->url() !== $genericRecord->url()) {
            return redirect($record->url());
        }

        $linked_families     = $this->linked_record_service->linkedFamilies($record);
        $linked_individuals  = $this->linked_record_service->linkedIndividuals($record);
        $linked_locations    = $this->linked_record_service->linkedLocations($record);
        $linked_media        = $this->linked_record_service->linkedMedia($record);
        $linked_notes        = $this->linked_record_service->linkedNotes($record);
        $linked_repositories = $this->linked_record_service->linkedRepositories($record);
        $linked_sources      = $this->linked_record_service->linkedSources($record);
        $linked_submitters   = $this->linked_record_service->linkedSubmitters($record);

        return $this->viewResponse('record-page', [
            'clipboard_facts'      => $this->clipboard_service->pastableFacts($record),
            'linked_families'      => $linked_families->isEmpty() ? null : $linked_families,
            'linked_individuals'   => $linked_individuals->isEmpty() ? null : $linked_individuals,
            'linked_locations'     => $linked_locations->isEmpty() ? null : $linked_locations,
            'linked_media_objects' => $linked_media->isEmpty() ? null : $linked_media,
            'linked_notes'         => $linked_notes->isEmpty() ? null : $linked_notes,
            'linked_repositories'  => $linked_repositories->isEmpty() ? null : $linked_repositories,
            'linked_sources'       => $linked_sources->isEmpty() ? null : $linked_sources,
            'linked_submitters'    => $linked_submitters->isEmpty() ? null : $linked_submitters,
            'record'               => $record,
            'title'                => $record->fullName(),
            'tree'                 => $tree,
        ]);
    }
}
