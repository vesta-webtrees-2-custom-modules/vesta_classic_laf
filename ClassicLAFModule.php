<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Aura\Router\Route;
use Cissee\Webtrees\Module\ClassicLAF\Factories\CustomXrefFactory;
use Cissee\Webtrees\Module\ClassicLAF\SurnameTradition\SurnameTraditionWrapper;
use Cissee\WebtreesExt\CustomFamilyFactory;
use Cissee\WebtreesExt\CustomIndividualFactory;
use Cissee\WebtreesExt\FamilyNameHandler;
use Cissee\WebtreesExt\GedcomRecordPageTempReplacement;
use Cissee\WebtreesExt\Http\RequestHandlers\AddChildToFamilyPageExt;
use Cissee\WebtreesExt\Http\RequestHandlers\AddChildToIndividualPageExt;
use Cissee\WebtreesExt\Http\RequestHandlers\AddNewFactExt;
use Cissee\WebtreesExt\Http\RequestHandlers\AddParentToIndividualPageExt;
use Cissee\WebtreesExt\Http\RequestHandlers\AddSpouseToFamilyPageExt;
use Cissee\WebtreesExt\Http\RequestHandlers\AddSpouseToIndividualPageExt;
use Cissee\WebtreesExt\Http\RequestHandlers\AddUnlinkedPageExt;
use Cissee\WebtreesExt\Http\RequestHandlers\ConfigGedcomField;
use Cissee\WebtreesExt\Http\RequestHandlers\ConfigGedcomFieldAction;
use Cissee\WebtreesExt\Http\RequestHandlers\EditFactPageExt;
use Cissee\WebtreesExt\IndividualExtSettings;
use Cissee\WebtreesExt\IndividualNameHandler;
use Cissee\WebtreesExt\Module\ModuleMetaInterface;
use Cissee\WebtreesExt\Module\ModuleMetaTrait;
use DOMXPath;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Http\Middleware\AuthAdministrator;
use Fisharebest\Webtrees\Http\Middleware\AuthEditor;
use Fisharebest\Webtrees\Http\RequestHandlers\AddChildToFamilyPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddChildToIndividualPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddNewFact;
use Fisharebest\Webtrees\Http\RequestHandlers\AddParentToIndividualPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddSpouseToFamilyPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddSpouseToIndividualPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddUnlinkedPage;
use Fisharebest\Webtrees\Http\RequestHandlers\EditFactPage;
use Fisharebest\Webtrees\Http\RequestHandlers\GedcomRecordPage;
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
use Fisharebest\Webtrees\SurnameTradition\DefaultSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\IcelandicSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\LithuanianSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\MatrilinealSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PaternalSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PatrilinealSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PolishSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PortugueseSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\SpanishSurnameTradition;
use Fisharebest\Webtrees\View;
use IvoPetkov\HTML5DOMDocument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaModuleTrait;
use function app;
use function route;

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
            //replace sub-views, individual-page itself is no longer ok (starting webtrees 2.1.2)
            if ($compactIndividualPage) {
                View::registerCustomView('::individual-page', $this->name() . '::individual-page');
                View::registerCustomView('::individual-page-title', $this->name() . '::individual-page-title');
            }
            View::registerCustomView('::individual-page-images', $this->name() . '::individual-page-images');
            if ($compactIndividualPage) {
                View::registerCustomView('::individual-page-names', $this->name() . '::individual-page-names');
            }
            View::registerCustomView('::individual-page-sidebars', $this->name() . '::individual-page-sidebars');
        }

        if ($compactIndividualPage) {
            View::registerCustomView('::individual-name', $this->name() . '::individual-name');
        }

        if (!$cropThumbnails) {
            View::registerCustomView('::chart-box', $this->name() . '::chart-box');

            View::registerCustomView('::selects/individual', $this->name() . '::selects/individual');
            View::registerCustomView('::selects/media', $this->name() . '::selects/media');
        }

        $strippedEdit = boolval($this->getPreference('COMPACT_EDIT', '1'));
        if ($strippedEdit) {
            //other custom modules also replace this view
            //namely the justLight theme      

            //[2022/08] actually apply the setting only in case of standard and other 'well-known' themes
            $apply = false;
            $isJustLight = false;
            
            $theme = app(ModuleThemeInterface::class);
            if ($theme instanceof ModuleCustomInterface) {
                //we need a way to identify custom modules regardless of their folder name
                
                //legacy  
                $justLightSupportUrl1 = 'https://github.com/justcarmen/webtrees-theme-justlight/issues';

                //current
                $justLightSupportUrl2 = 'https://justcarmen.nl/modules-webtrees-2/justlight-theme/';

                if ($theme->customModuleSupportUrl() === $justLightSupportUrl1) {
                    $apply = true;
                    $isJustLight = true;
                } else if ($theme->customModuleSupportUrl() === $justLightSupportUrl2) {
                    $apply = true;
                    $isJustLight = true;
                }
                
            } else {
                //standard theme
                $apply = true;
            }
            
            if ($apply) {
                $defaultLayout = '::layouts/default';
                
                if ($isJustLight) {
                    $defaultLayout = '::layouts/defaultJustLight';
                }
                
                View::registerCustomView('::layouts/default', $this->name() . $defaultLayout);
            }
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

        //temp workaround for #79 start
        //keep until https://github.com/fisharebest/webtrees/issues/4383 is resolved
        $router = Registry::routeFactory()->routeMap();
      
        //we have to remove the original route, otherwise: RouteAlreadyExists (meh)
        $existingRoutes = $router->getRoutes();
        if (array_key_exists(GedcomRecordPage::class, $existingRoutes)) {
            unset($existingRoutes[GedcomRecordPage::class]);        
        }
        
        $router->setRoutes($existingRoutes);
        
        $router->get(GedcomRecordPage::class, '/tree/{tree}/record/{xref}{/slug}', GedcomRecordPageTempReplacement::class);    

        //temp workaround for #79 end        
        
        Registry::familyFactory(new CustomFamilyFactory());

        $customPrefixes = boolval($this->getPreference('CUSTOM_PREFIXES', '0'));
        if ($customPrefixes) {
            Registry::xrefFactory(new CustomXrefFactory($this));
        }

        $skipNameType = boolval($this->getPreference('SKIP_NAME_TYPE', '0'));
        if ($skipNameType) {
            $stf = Registry::surnameTraditionFactory();
            $stf->register($stf::PATERNAL, new SurnameTraditionWrapper(new PaternalSurnameTradition()));
            $stf->register($stf::PATRILINEAL, new SurnameTraditionWrapper(new PatrilinealSurnameTradition()));
            $stf->register($stf::MATRILINEAL, new SurnameTraditionWrapper(new MatrilinealSurnameTradition()));
            $stf->register($stf::PORTUGUESE, new SurnameTraditionWrapper(new PortugueseSurnameTradition()));
            $stf->register($stf::SPANISH, new SurnameTraditionWrapper(new SpanishSurnameTradition()));
            $stf->register($stf::POLISH, new SurnameTraditionWrapper(new PolishSurnameTradition()));
            $stf->register($stf::LITHUANIAN, new SurnameTraditionWrapper(new LithuanianSurnameTradition()));
            $stf->register($stf::ICELANDIC, new SurnameTraditionWrapper(new IcelandicSurnameTradition()));
            $stf->register($stf::DEFAULT, new SurnameTraditionWrapper(new DefaultSurnameTradition()));
        }
        
        //advanced configuration of fact subtags start
        
        //for config (notes only)
        View::registerCustomView('::admin/tags', $this->name() . '::admin/tags-ext');
        
        //for config
        View::registerCustomView('::edit/edit-gedcom-fields', $this->name() . '::edit/edit-gedcom-fields-switch');
        View::registerCustomView('::edit/edit-gedcom-fields-ext', $this->name() . '::edit/edit-gedcom-fields-ext');
        View::registerCustomView('::edit/edit-gedcom-fields-ext2', $this->name() . '::edit/edit-gedcom-fields-ext2');
        View::registerCustomView('::edit/icon-config-gedcom-field', $this->name() . '::edit/icon-config-gedcom-field');
        View::registerCustomView('::icons/config-gedcom-field', $this->name() . '::icons/config-gedcom-field');
        View::registerCustomView('::modals/config-gedcom-field', $this->name() . '::modals/config-gedcom-field');
        View::registerCustomView('::edit/config-gedcom-field-edit-control', $this->name() . '::edit/config-gedcom-field-edit-control');
        View::registerCustomView('::edit/config-gedcom-field-edit-control-2', $this->name() . '::edit/config-gedcom-field-edit-control-2');
        
        $router->get(ConfigGedcomField::class, '/tree/{tree}/config-gedcom-field/{tag}/{indent}', ConfigGedcomField::class)
            ->extras(['middleware' => [AuthAdministrator::class]]);        
        
        $router->post(ConfigGedcomFieldAction::class, '/tree/{tree}/config-gedcom-field-action', ConfigGedcomFieldAction::class)
            ->extras(['middleware' => [AuthAdministrator::class]]);        
        
        //we have to remove the original route, otherwise: RouteAlreadyExists (meh)
        $existingRoutes = $router->getRoutes();
        //for display and config
        if (array_key_exists(AddNewFact::class, $existingRoutes)) {
            unset($existingRoutes[AddNewFact::class]);        
        }
        //for display and config
        if (array_key_exists(EditFactPage::class, $existingRoutes)) {
            unset($existingRoutes[EditFactPage::class]);        
        }
        //for display and config
        if (array_key_exists(EditFactPage::class, $existingRoutes)) {
            unset($existingRoutes[EditFactPage::class]);        
        }
        
        //for display
        if (array_key_exists(AddChildToFamilyPage::class, $existingRoutes)) {
            unset($existingRoutes[AddChildToFamilyPage::class]);        
        }
        //for display
        if (array_key_exists(AddChildToIndividualPage::class, $existingRoutes)) {
            unset($existingRoutes[AddChildToIndividualPage::class]);        
        }
        //for display
        if (array_key_exists(AddParentToIndividualPage::class, $existingRoutes)) {
            unset($existingRoutes[AddParentToIndividualPage::class]);        
        }
        //for display
        if (array_key_exists(AddSpouseToFamilyPage::class, $existingRoutes)) {
            unset($existingRoutes[AddSpouseToFamilyPage::class]);        
        }
        //for display
        if (array_key_exists(AddSpouseToIndividualPage::class, $existingRoutes)) {
            unset($existingRoutes[AddSpouseToIndividualPage::class]);        
        }
        //for display
        if (array_key_exists(AddUnlinkedPage::class, $existingRoutes)) {
            unset($existingRoutes[AddUnlinkedPage::class]);        
        }
        
        $router->setRoutes($existingRoutes);
        
        //for display and config
        $router->get(AddNewFact::class, '/tree/{tree}/add-fact/{xref}/{fact}', AddNewFactExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //for display and config
        $router->get(EditFactPage::class, '/tree/{tree}/edit-fact/{xref}/{fact_id}', EditFactPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //for display
        $router->get(AddChildToFamilyPage::class, '/tree/{tree}/add-child-to-family/{xref}/{sex}', AddChildToFamilyPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //for display
        $router->get(AddChildToIndividualPage::class, '/tree/{tree}/add-child-to-individual/{xref}', AddChildToIndividualPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //for display
        $router->get(AddParentToIndividualPage::class, '/tree/{tree}/add-parent-to-individual/{xref}/{sex}', AddParentToIndividualPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //for display
        $router->get(AddSpouseToFamilyPage::class, '/tree/{tree}/add-spouse-to-family/{xref}/{sex}', AddSpouseToFamilyPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //for display
        $router->get(AddSpouseToIndividualPage::class, '/tree/{tree}/add-spouse-to-individual/{xref}', AddSpouseToIndividualPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        $router->get(AddUnlinkedPage::class, '/tree/{tree}/add-unlinked-individual', AddUnlinkedPageExt::class)
            ->extras(['middleware' => [AuthEditor::class]]);
        
        //advanced configuration of fact subtags end
        
        $this->flashWhatsNew('\Cissee\Webtrees\Module\ClassicLAF\WhatsNew', 4);
    }

    public function headContent(): string {
        $html = '<link rel="stylesheet" type="text/css" href="' . $this->assetUrl('css/theme.css') . '">';

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
