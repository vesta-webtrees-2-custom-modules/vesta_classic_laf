<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\RequestHandlers\AddNewFact;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaUtils;

class AddNewFactExt implements RequestHandlerInterface {
    
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $ext = new GedcomEditServiceExt2(true);
        
        //explicitly register in order to re-use in views where we cannot pass via variable
        VestaUtils::set(GedcomEditServiceExt2::class, $ext);
        
        $include_hidden = (bool) ($request->getQueryParams()['include_hidden'] ?? false);

        $can_configure = Auth::isAdmin() && $include_hidden;

        if ($can_configure) {
            //explicitly register in order to re-use in views where we cannot pass via variable
            VestaUtils::set(EditGedcomFieldsArgs::class, new EditGedcomFieldsArgs(true));
        }
        
        $handler = new AddNewFact($ext);
        return $handler->handle($request);
    }
}
