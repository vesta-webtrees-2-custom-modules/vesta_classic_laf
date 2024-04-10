<?php


declare(strict_types=1);

namespace Cissee\WebtreesExt\Http\RequestHandlers;

use Cissee\WebtreesExt\Services\GedcomEditServiceExt2;
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

        $tag2parts = explode(':',$tag);
        $tag2parts[1] = '*';
        $tag2 = implode(':',$tag2parts);
        $value2 = $params['CONFIG_GEDCOM_FIELDS_2'];

        $gedcom_edit_service = new GedcomEditServiceExt2();
        $gedcom_edit_service->setPreference($tree, $tag, $value);
        $gedcom_edit_service->setPreference($tree, $tag2, $value2);

        // value and text are for autocomplete
        // html is for interactive modals
        return response();
    }
}
