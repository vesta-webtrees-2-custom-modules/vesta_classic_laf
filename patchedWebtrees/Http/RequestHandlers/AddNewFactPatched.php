<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Http\RequestHandlers\AddNewFact;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\GedcomEditService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function route;
use function trim;

//same as webtrees AddNewFact, but non-final
//also adjust:
//route(self::class
class AddNewFactPatched implements RequestHandlerInterface
{
    use ViewResponseTrait;

    public function __construct(
        private readonly GedcomEditService $gedcom_edit_service,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree   = Validator::attributes($request)->tree();
        $xref   = Validator::attributes($request)->isXref()->string('xref');
        $subtag = Validator::attributes($request)->isTag()->string('fact');

        if ($subtag === 'OBJE' && !Auth::canUploadMedia($tree, Auth::user())) {
            throw new HttpAccessDeniedException();
        }

        $include_hidden = Validator::queryParams($request)->boolean('include_hidden', false);

        $record  = Registry::gedcomRecordFactory()->make($xref, $tree);
        $record  = Auth::checkRecordAccess($record, true);
        $element = Registry::elementFactory()->make($record->tag() . ':' . $subtag);
        $title   = $record->fullName() . ' - ' . $element->label();
        $fact    = new Fact(trim('1 ' . $subtag . ' ' . $element->default($tree)), $record, 'new');
        $gedcom  = $this->gedcom_edit_service->insertMissingFactSubtags($fact, $include_hidden);
        $hidden  = $this->gedcom_edit_service->insertMissingFactSubtags($fact, true);
        $url     = $record->url();

        if ($gedcom === $hidden) {
            $hidden_url = '';
        } else {
            $hidden_url = route(AddNewFact::class, [
                'fact'           => $subtag,
                'include_hidden' => true,
                'tree'           => $tree->name(),
                'xref'           => $xref,
            ]);
        }

        return $this->viewResponse('edit/edit-fact', [
            'can_edit_raw' => false,
            'fact'         => $fact,
            'gedcom'       => $gedcom,
            'hidden_url'   => $hidden_url,
            'title'        => $title,
            'tree'         => $tree,
            'url'          => $url,
        ]);
    }
}
