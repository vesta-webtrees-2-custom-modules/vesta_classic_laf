<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\RequestHandlers\AddSpouseToIndividualAction;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\GedcomEditService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function route;

//same as webtrees AddSpouseToIndividualPage, but non-final
class AddSpouseToIndividualPagePatched implements RequestHandlerInterface
{
    use ViewResponseTrait;

    // Create mixed-sex couples by default
    private const array OPPOSITE_SEX = [
        'F' => 'M',
        'M' => 'F',
        'U' => 'U',
        'X' => 'U',
    ];

    public function __construct(
        private readonly GedcomEditService $gedcom_edit_service,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree       = Validator::attributes($request)->tree();
        $xref       = Validator::attributes($request)->isXref()->string('xref');
        $individual = Registry::individualFactory()->make($xref, $tree);
        $individual = Auth::checkIndividualAccess($individual, true);

        $sex = self::OPPOSITE_SEX[$individual->sex()];

        // Name facts.
        $surname_tradition = Registry::surnameTraditionFactory()
            ->make($tree->getPreference('SURNAME_TRADITION'));

        $names = $surname_tradition->newSpouseNames($individual, $sex);

        $facts = [
            'i' => $this->gedcom_edit_service->newIndividualFacts($tree, $sex, $names),
            'f' => $this->gedcom_edit_service->newFamilyFacts($tree),
        ];

        $titles = [
            'F' => I18N::translate('Add a wife'),
            'M' => I18N::translate('Add a husband'),
            'U' => I18N::translate('Add a spouse'),
        ];

        $title = $titles[$sex];

        return $this->viewResponse('edit/new-individual', [
            'facts'               => $facts,
            'gedcom_edit_service' => $this->gedcom_edit_service,
            'post_url'            => route(AddSpouseToIndividualAction::class, ['tree' => $tree->name(), 'xref' => $xref]),
            'title'               => $individual->fullName() . ' - ' . $title,
            'tree'                => $tree,
            'url'                 => Validator::queryParams($request)->isLocalUrl()->string('url', $individual->url()),
        ]);
    }
}
