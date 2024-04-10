<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\RequestHandlers\AddNewFact;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function app;

class AddNewFactExt extends AddNewFact
{
    public function __construct()
    {
        parent::__construct(
            //check overrides
            new GedcomEditServiceExt2());
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //explicitly register in order to re-use in views where we cannot pass via variable
        app()->instance(GedcomEditServiceExt2::class, new GedcomEditServiceExt2());

        $include_hidden = (bool) ($request->getQueryParams()['include_hidden'] ?? false);

        $can_configure = Auth::isAdmin() && $include_hidden;

        if ($can_configure) {
            //explicitly register in order to re-use in views where we cannot pass via variable
            app()->instance(EditGedcomFieldsArgs::class, new EditGedcomFieldsArgs(true));
        }

        return parent::handle($request);
    }
}
