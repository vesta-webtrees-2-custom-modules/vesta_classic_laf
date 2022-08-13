<?php

declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;


class ConfigGedcomField implements RequestHandlerInterface {
    
    use ViewResponseTrait;
    
    public function handle(ServerRequestInterface $request): ResponseInterface {
        
        $tree = Validator::attributes($request)->tree();
        $tag = Validator::attributes($request)->string('tag');
        $tag2parts = explode(':',$tag);
        $tag2parts[1] = '*';
        $tag2 = implode(':',$tag2parts);
        
        $html = view('modals/config-gedcom-field', [
            'tree' => $tree,
            'tag' => $tag,
            'tag2' => $tag2,
        ]);
        
        return response($html);
    }
}
