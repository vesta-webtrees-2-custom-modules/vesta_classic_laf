<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\GedcomEditService;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function route;

//same as webtrees AddUnlinkedPage, but non-final
class AddUnlinkedPagePatched implements RequestHandlerInterface
{
    use ViewResponseTrait;

    public function __construct(
        private readonly GedcomEditService $gedcom_edit_service,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $sex  = Registry::elementFactory()->make('INDI:SEX')->default($tree);
        $name = Registry::elementFactory()->make('INDI:NAME')->default($tree);

        $facts = [
            'i' => $this->gedcom_edit_service->newIndividualFacts($tree, $sex, ['1 NAME ' . $name]),
        ];

        $url = route(ManageTrees::class, ['tree' => $tree->name()]);

        return $this->viewResponse('edit/new-individual', [
            'facts'               => $facts,
            'gedcom_edit_service' => $this->gedcom_edit_service,
            'post_url'            => route(AddUnlinkedAction::class, ['tree' => $tree->name()]),
            'tree'                => $tree,
            'title'               => I18N::translate('Create an individual'),
            'url'                 => Validator::queryParams($request)->isLocalUrl()->string('url', $url),
        ]);
    }
}
