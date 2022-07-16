<?php


declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function response;

class ConfigGedcomFieldAction implements RequestHandlerInterface {

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $tree = Validator::attributes($request)->tree();
        $params = (array) $request->getParsedBody();
        
        $tag = $params['tag'];
        $value = $params['CONFIG_GEDCOM_FIELDS'];

        $gedcom_edit_service = new GedcomEditServiceExt();
        $gedcom_edit_service->setPreference($tree, $tag, $value);

        // value and text are for autocomplete
        // html is for interactive modals
        return response();
    }
}
