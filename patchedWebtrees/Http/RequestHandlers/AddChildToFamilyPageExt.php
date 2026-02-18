<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
use Fisharebest\Webtrees\Http\RequestHandlers\AddChildToFamilyPage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaUtils;

class AddChildToFamilyPageExt implements RequestHandlerInterface {
    
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $ext = new GedcomEditServiceExt2(true);
        
        //explicitly register in order to re-use in views where we cannot pass via variable
        VestaUtils::set(GedcomEditServiceExt2::class, $ext);
        
        $handler = new AddChildToFamilyPage($ext);
        return $handler->handle($request);
    }
}
