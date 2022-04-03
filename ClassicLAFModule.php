<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Aura\Router\Route;
use Cissee\Webtrees\Module\ClassicLAF\Factories\CustomXrefFactory;
use Cissee\WebtreesExt\CustomFamilyFactory;
use Cissee\WebtreesExt\CustomIndividualFactory;
use Cissee\WebtreesExt\FamilyNameHandler;
use Cissee\WebtreesExt\IndividualExtSettings;
use Cissee\WebtreesExt\IndividualNameHandler;
use Cissee\WebtreesExt\Module\ModuleMetaInterface;
use Cissee\WebtreesExt\Module\ModuleMetaTrait;
use DOMXPath;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Http\Middleware\AuthEditor;
use Fisharebest\Webtrees\Http\RequestHandlers\SearchReplacePage;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Webtrees;
use IvoPetkov\HTML5DOMDocument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaModuleTrait;
use function app;
use function route;
use function str_starts_with;

class ClassicLAFModule extends AbstractModule implements
ModuleCustomInterface, ModuleMetaInterface, ModuleConfigInterface, ModuleGlobalInterface/* ,
  MiddlewareInterface */ {

    use ModuleCustomTrait,
        ModuleMetaTrait,
        ModuleConfigTrait,
        ModuleGlobalTrait,
        VestaModuleTrait {
        VestaModuleTrait::customTranslations insteadof ModuleCustomTrait;
        VestaModuleTrait::getAssetAction insteadof ModuleCustomTrait;
        VestaModuleTrait::assetUrl insteadof ModuleCustomTrait;
        VestaModuleTrait::getConfigLink insteadof ModuleConfigTrait;
        ModuleMetaTrait::customModuleVersion insteadof ModuleCustomTrait;
        ModuleMetaTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
    }

    use ClassicLAFModuleTrait;

    public function customModuleAuthorName(): string {
        return 'Richard Cissée';
    }

    public function customModuleMetaDatasJson(): string {
        return file_get_contents(__DIR__ . '/metadata.json');
    }

    public function customModuleLatestMetaDatasJsonUrl(): string {
        return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_classic_laf/master/metadata.json';
    }

    public function customModuleSupportUrl(): string {
        return 'https://cissee.de';
    }

    public function resourcesFolder(): string {
        return __DIR__ . '/resources/';
    }

    public function onBoot(): void {

        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

        $compactIndividualPage = boolval($this->getPreference('COMPACT_INDI_PAGE', '1'));
        $cropThumbnails = boolval($this->getPreference('CROP_THUMBNAILS', '1'));
        $expandFirstSidebar = boolval($this->getPreference('EXPAND_FIRST_SIDEBAR', '0'));

        if ($compactIndividualPage || !$cropThumbnails) {
            // Replace an existing view with our own version.
            if (str_starts_with(Webtrees::VERSION, '2.1')) {
                //replace sub-views, individual-page itself is ok
                if ($compactIndividualPage) {
                    View::registerCustomView('::individual-page-title', $this->name() . '::individual-page-title');
                }
                View::registerCustomView('::individual-page-images', $this->name() . '::individual-page-images');
                if ($compactIndividualPage) {
                    View::registerCustomView('::individual-page-names', $this->name() . '::individual-page-names');
                }
                View::registerCustomView('::individual-page-sidebars', $this->name() . '::individual-page-sidebars');
            } else {
                View::registerCustomView('::individual-page', $this->name() . '::individual-page_20');
            }
        }

        if ($compactIndividualPage) {
            if (str_starts_with(Webtrees::VERSION, '2.1')) {
                View::registerCustomView('::individual-name', $this->name() . '::individual-name');
            } else {
                View::registerCustomView('::individual-name', $this->name() . '::individual-name_20');
            }
        }

        if (!$cropThumbnails) {
            if (str_starts_with(Webtrees::VERSION, '2.1')) {
                View::registerCustomView('::chart-box', $this->name() . '::chart-box');
            } else {
                View::registerCustomView('::chart-box', $this->name() . '::chart-box_20');
            }

            View::registerCustomView('::selects/individual', $this->name() . '::selects/individual');
            View::registerCustomView('::selects/media', $this->name() . '::selects/media');
        }

        $strippedEdit = boolval($this->getPreference('COMPACT_EDIT', '1'));
        if ($strippedEdit) {
            //other custom modules also replace this view
            //namely the justLight theme      

            if (str_starts_with(Webtrees::VERSION, '2.1')) {
                $defaultLayout = '::layouts/default';
            } else {
                $defaultLayout = '::layouts/default_20';
            }

            //we need a way to identify it regardless of its folder name;
            $theme = app(ModuleThemeInterface::class);
            if ($theme instanceof ModuleCustomInterface) {
                //legacy  
                $justLightSupportUrl1 = 'https://github.com/justcarmen/webtrees-theme-justlight/issues';

                //current
                $justLightSupportUrl2 = 'https://justcarmen.nl/modules-webtrees-2/justlight-theme/';

                if ($theme->customModuleSupportUrl() === $justLightSupportUrl1) {
                    $defaultLayout = '::layouts/defaultJustLight';
                } else if ($theme->customModuleSupportUrl() === $justLightSupportUrl2) {
                    $defaultLayout = '::layouts/defaultJustLight';
                }
            }

            View::registerCustomView('::layouts/default', $this->name() . $defaultLayout);
        }

        //TODO CLEANUP
        /*
          $markdownFixConfigured = boolval($this->getPreference('MARKDOWN_PRESERVE_CONT', '1'));
          if ($markdownFixConfigured) {
          Registry::markdownFactory(new CustomMarkdownFactory());
          }
         */

        $individualNameHandler = app(IndividualNameHandler::class);

        $nickBeforeSurn = boolval($this->getPreference('NICK_BEFORE_SURN', '1'));
        $individualNameHandler->setNickBeforeSurn($nickBeforeSurn);
        $appendXrefIndi = boolval($this->getPreference('APPEND_XREF', '0'));
        $individualNameHandler->setAppendXref($appendXrefIndi);

        $familyNameHandler = app(FamilyNameHandler::class);
        $appendXrefFam = boolval($this->getPreference('APPEND_XREF_FAM', '0'));
        $familyNameHandler->setAppendXref($appendXrefFam);

        //must explicitly register in order to re-use elsewhere! (see webtrees #3085)
        app()->instance(IndividualNameHandler::class, $individualNameHandler);
        app()->instance(FamilyNameHandler::class, $familyNameHandler);

        Registry::individualFactory(new CustomIndividualFactory(
                new IndividualExtSettings($compactIndividualPage, $cropThumbnails, $expandFirstSidebar)));

        Registry::familyFactory(new CustomFamilyFactory());

        $customPrefixes = boolval($this->getPreference('CUSTOM_PREFIXES', '0'));
        if ($customPrefixes) {
            Registry::xrefFactory(new CustomXrefFactory($this));
        }

        $this->flashWhatsNew('\Cissee\Webtrees\Module\ClassicLAF\WhatsNew', 4);
    }

    public function headContent(): string {
        $html = '<link rel="stylesheet" type="text/css" href="' . $this->assetUrl('css/theme.css') . '">';

        //TODO cleanup
        /*
        $markdownFixConfigured = boolval($this->getPreference('MARKDOWN_PRESERVE_CONT', '1'));
        if ($markdownFixConfigured) {
            //css fix as suggested here https://www.webtrees.net/index.php/en/forum/help-for-2-0/36162-v2-0-17-things-that-no-longer-work-after-update#88287
            //has to be blocked because it leads to double line breaks
            $html .= '<link rel="stylesheet" type="text/css" href="' . $this->assetUrl('css/blockMarkdownFix.css') . '">';
        }
        */        

        return $html;
    }

    public function assetsViaViews(): array {
        return [
            'css/theme.css' => 'css/theme'];
    }

    public function assetAdditionalHash(string $asset): string {
        //view is dynamic - we have to hash properly!
        //$compactEdit is switched elsewhere    
        $fullWidth = boolval($this->getPreference('FULL_WIDTH', '1'));
        $compactIndividualPage = boolval($this->getPreference('COMPACT_INDI_PAGE', '1'));

        return "FULL_WIDTH:" . $fullWidth . ";COMPACT_INDI_PAGE:" . $compactIndividualPage . ";";
    }

    public function getConfigLink(): string {
        return route('module', [
            'module' => $this->name(),
            'action' => 'Admin',
        ]);
    }

    public static function isEditDialogToBeStripped(ServerRequestInterface $request): bool {
        $route = $request->getAttribute('route');
        assert($route instanceof Route);
        $route_middleware = $route->extras['middleware'] ?? [];

        //$route_handler is a string, not an instance of AuthEditor!
        $route_handler = $route->handler;
        if ($route_handler === SearchReplacePage::class) {
            //special - not a regular edit dialog:
            //more consistent look & feel if displayed just like other searches (issue #49)
            return false;
        }

        foreach ($route_middleware as $middleware) {
            //all edit routes have this middleware, see WebRoutes.php
            //$middleware is a string, not an instance of AuthEditor!
            if ($middleware === AuthEditor::class) {
                return true;
            }
        }

        return false;
    }

    //we use a better solution now: we make the default layout configurable
    //(this is only problematic if another module also does this, e.g. as suggested in 
    //https://www.webtrees.net/index.php/en/forum/help-for-2-0/36114-add-one-more-line)
    public function process_obsolete(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler): ResponseInterface {

        /*
          if ($route->handler === EditFact::class) {
          //TODO requires WrapHandler update! Otherwise no effect.
          //discussed in https://github.com/fisharebest/webtrees/issues/3339
          $route->handler(EditFactAdjusted::class); //note - we could use a single wrapper class for all targets here, no need to have one per target

          //not even required, $request is mutable
          //$request = $request->withAttribute('route', $route);
          }
          //seems easier, but certainly less elegant, to strip header and footer from generated html, and adjust some css
          //although this is error-prone (see issue #51)
         */

        $strippedEdit = boolval($this->getPreference('COMPACT_EDIT', '1'));

        if (!ClassicLAFModuleTrait::checkLibxml()) {
            $strippedEdit = false;
        }

        $response = $handler->handle($request);

        if ($strippedEdit && ($response->getStatusCode() === StatusCodeInterface::STATUS_OK)) {
            $strip = ClassicLAFModule::isEditDialogToBeStripped($request);

            if ($strip) {
                //must not adjust json responses
                $contentType = $response->getHeaderLine("Content-Type");
                if (substr($contentType, 0, strlen("text/html")) === "text/html") {
                    $html = $response->getBody()->__toString();
                    $content = ClassicLAFModule::strippedLayout_obsolete($html);
                    $stream_factory = app(StreamFactoryInterface::class);
                    $stream = $stream_factory->createStream($content);
                    $response = $response->withBody($stream);

                    //adjust header
                    $response = $response->withHeader('Content-Length', (string) strlen($content));
                }
            }
        }

        return $response;
    }

    public static function strippedLayout_obsolete(string $html): string {
        //$dom=new DOMDocument(); //doesn't handle HTML 5 entities , such as &utilde; correctly
        //see https://stackoverflow.com/questions/43469435/phps-domdocument-appears-to-not-recognize-certain-html-entities-how-can-i-incl
        //and https://3v4l.org/tMXTt
        //fix #31 by using HTML5DOMDocument instead of DOMDocument
        $dom = new HTML5DOMDocument();

        $dom->validateOnParse = false;
        $internalErrors = libxml_use_internal_errors(true);

        //have to prefix to force utf-8 (relevant e.g. for modals)
        //cf https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        //fix #43
        //webtrees uses hash of fact contents as id, which results in invalid html in case of repeated facts!
        //this should be handled differently in webtrees. Until then, ALLOW_DUPLICATE_IDS
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
        libxml_use_internal_errors($internalErrors);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//header');

        if (!empty($nodes)) {
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        $nodes = $xpath->query('//footer');

        if (!empty($nodes)) {
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        //also strip the cookie-warning script (added via footer), the respective element has been stripped
        $nodes = $xpath->query('//script[contains(text(), "cookie-warning")]');

        if (!empty($nodes)) {
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        $nodes = $xpath->query('//div[@class = "container-lg wt-main-container"]');

        if (!empty($nodes)) {
            foreach ($nodes as $node) {
                $node->setAttribute("class", "container-lg edit-container wt-main-container");
            }
        }

        //using $dom->saveHTML(); replaces utf-8 special characters with html entities
        //cf https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
        //doctype also has to be restored explicitly
        return '<!DOCTYPE html>' . $dom->saveHTML($dom->documentElement);
    }

}
