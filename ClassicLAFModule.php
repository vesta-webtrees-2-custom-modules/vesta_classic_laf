<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Aura\Router\Route;
use Cissee\Webtrees\Module\ClassicLAF\Factories\CustomXrefFactory;
use Cissee\Webtrees\Module\ClassicLAF\SurnameTradition\SurnameTraditionWrapper;
use Cissee\WebtreesExt\CustomFamilyFactory;
use Cissee\WebtreesExt\CustomIndividualFactory;
use Cissee\WebtreesExt\Elements\Level1NoteStructure;
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
use Cissee\WebtreesExt\Http\RequestHandlers\EditMainFieldsAction;
use Cissee\WebtreesExt\Http\RequestHandlers\EditMainFieldsPage;
use Cissee\WebtreesExt\IndividualExtSettings;
use Cissee\WebtreesExt\IndividualNameHandler;
use Cissee\WebtreesExt\Module\ModuleMetaInterface;
use Cissee\WebtreesExt\Module\ModuleMetaTrait;
use Cissee\WebtreesExt\MoreI18N;
use DOMXPath;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\Middleware\AuthAdministrator;
use Fisharebest\Webtrees\Http\Middleware\AuthEditor;
use Fisharebest\Webtrees\Http\RequestHandlers\AddChildToFamilyPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddChildToIndividualPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddNewFact;
use Fisharebest\Webtrees\Http\RequestHandlers\AddParentToIndividualPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddSpouseToFamilyPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddSpouseToIndividualPage;
use Fisharebest\Webtrees\Http\RequestHandlers\AddUnlinkedPage;
use Fisharebest\Webtrees\Http\RequestHandlers\ControlPanel;
use Fisharebest\Webtrees\Http\RequestHandlers\EditFactPage;
use Fisharebest\Webtrees\Http\RequestHandlers\GedcomRecordPage;
use Fisharebest\Webtrees\Http\RequestHandlers\SearchReplacePage;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\HtmlService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Site;
use Fisharebest\Webtrees\SurnameTradition\DefaultSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\IcelandicSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\LithuanianSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\MatrilinealSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PaternalSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PatrilinealSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PolishSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\PortugueseSurnameTradition;
use Fisharebest\Webtrees\SurnameTradition\SpanishSurnameTradition;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\View;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use IvoPetkov\HTML5DOMDocument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaModuleTrait;
use function app;
use function redirect;
use function response;
use function route;

class ClassicLAFModule extends AbstractModule implements
    ModuleCustomInterface,
    ModuleMetaInterface,
    ModuleConfigInterface,
    ModuleGlobalInterface/* ,
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
        //explicitly register in order to re-use in views where we cannot pass via variable
        //(could also resolve via module service)
        app()->instance(ClassicLAFModule::class, $this); //do not use bind()! for some reason leads to 'Illegal offset type in isset or empty'

        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

        $compactIndividualPage = intval($this->getPreference('COMPACT_INDI_PAGE', '1'));
        $cropThumbnails = boolval($this->getPreference('CROP_THUMBNAILS', '1'));
        $expandFirstSidebar = boolval($this->getPreference('EXPAND_FIRST_SIDEBAR', '0'));

        if (($compactIndividualPage > 0) || !$cropThumbnails) {
            // Replace an existing view with our own version.
            //replace sub-views, individual-page itself is no longer ok (starting webtrees 2.1.2)
            if ($compactIndividualPage > 0) {
                View::registerCustomView('::individual-page', $this->name() . '::individual-page');
                View::registerCustomView('::individual-page-title', $this->name() . '::individual-page-title');
            }
            View::registerCustomView('::individual-page-images', $this->name() . '::individual-page-images');
            if ($compactIndividualPage > 0) {
                View::registerCustomView('::individual-page-names', $this->name() . '::individual-page-names');
            }
            View::registerCustomView('::individual-page-sidebars', $this->name() . '::individual-page-sidebars');
        }

        //Issue 135: more fine-grained control over this part
        //in order to allow adjustments by other custom modules
        if ($compactIndividualPage === 1) {
            View::registerCustomView('::individual-page-name', $this->name() . '::individual-name');
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

                $justLightSupportUrl = 'https://justcarmen.nl/modules-webtrees-2/justlight-theme/';
                $supportedVersion = version_compare($theme->customModuleVersion(), '2.2.8');

                if (($theme->customModuleSupportUrl() === $justLightSupportUrl) && ($supportedVersion >= 0)) {
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

        $self = $this;
        $individualNameHandler->setAddBadgesCallback(static function (Tree $tree, string $gedcom) use ($self) {
            return $self->addBadges($tree, $gedcom);
        });

        $familyNameHandler = app(FamilyNameHandler::class);
        $appendXrefFam = boolval($this->getPreference('APPEND_XREF_FAM', '0'));
        $familyNameHandler->setAppendXref($appendXrefFam);

        //must explicitly register in order to re-use elsewhere! (see webtrees #3085)
        app()->instance(IndividualNameHandler::class, $individualNameHandler);
        app()->instance(FamilyNameHandler::class, $familyNameHandler);

        Registry::individualFactory(new CustomIndividualFactory(
                new IndividualExtSettings(($compactIndividualPage > 0), $cropThumbnails, $expandFirstSidebar)));

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

        View::registerCustomView('::individual-page-menu', $this->name() . '::individual-page-menu');
        View::registerCustomView('::family-page-menu', $this->name() . '::family-page-menu');
        View::registerCustomView('::edit/existing-record', $this->name() . '::edit/existing-record');

        $router->get(EditMainFieldsPage::class, '/tree/{tree}/edit-main/{xref}')
            ->extras(['middleware' => [AuthEditor::class]]);

        $router->post(EditMainFieldsAction::class, '/tree/{tree}/edit-main/{xref}')
            ->extras(['middleware' => [AuthEditor::class]]);

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

        $ef = Registry::elementFactory();

        //larger textarea for level 1 notes
        $ef->registerTags([
            'INDI:NOTE' => new Level1NoteStructure(MoreI18N::xlate('Note')),
            'FAM:NOTE' => new Level1NoteStructure(MoreI18N::xlate('Note')),
        ]);

        ////

        $pref = 'WHATS_NEW';
        $current_version = intval($this->getPreference($pref, '0'));
        if ($current_version < 5) {
            $this->setInitialNameBadges($current_version > 0);
        }

        ////

        $this->flashWhatsNew('\Cissee\Webtrees\Module\ClassicLAF\WhatsNew', 5);
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
        $compactIndividualPage = intval($this->getPreference('COMPACT_INDI_PAGE', '1'));

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

    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////

    protected function editConfigAfterFaq() {
        $url = route('module', [
            'module' => $this->name(),
            'action' => 'Admin2'
        ]);
        ?>
        <h1><a href="<?php echo $url; ?>"><?php echo I18N::translate('Name badges'); ?></a></h1>
        <p class "text-muted"><?= I18N::translate('Name badges are HTML snippets, e.g. small images, displayed after an individual\'s name.') ?></p>
        <?php
    }

    //everything below adapted from FrequentlyAskedQuestionsModule

    private HtmlService $html_service;
    private TreeService $tree_service;

    /**
     * @param HtmlService $html_service
     * @param TreeService $tree_service
     */
    public function __construct(HtmlService $html_service, TreeService $tree_service)
    {
        $this->html_service = $html_service;
        $this->tree_service = $tree_service;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getAdmin2Action(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        // This module can't run without a tree
        $tree = Validator::attributes($request)->treeOptional();

        if (!$tree instanceof Tree) {
            $trees = $this->tree_service->all();

            $tree = $trees->get(Site::getPreference('DEFAULT_GEDCOM')) ?? $trees->first();

            if ($tree instanceof Tree) {
                return redirect(route('module', ['module' => $this->name(), 'action' => 'Admin2', 'tree' => $tree->name()]));
            }

            return redirect(route(ControlPanel::class));
        }

        $nameBadges = $this->nameBadgesForTree($tree);

        $min_block_order = (int) DB::table('block')
            ->where('module_name', '=', $this->name())
            ->where(static function (Builder $query) use ($tree): void {
                $query
                    ->whereNull('gedcom_id')
                    ->orWhere('gedcom_id', '=', $tree->id());
            })
            ->min('block_order');

        $max_block_order = (int) DB::table('block')
            ->where('module_name', '=', $this->name())
            ->where(static function (Builder $query) use ($tree): void {
                $query
                    ->whereNull('gedcom_id')
                    ->orWhere('gedcom_id', '=', $tree->id());
            })
            ->max('block_order');

        $title = I18N::translate('Name badges') . ' — ' . $tree->title();

        return $this->viewResponse($this->name() . '::admin/badges/config', [
            'action'          => route('module', ['module' => $this->name(), 'action' => 'Admin2']),
            'nameBadges'      => $nameBadges,
            'max_block_order' => $max_block_order,
            'min_block_order' => $min_block_order,
            'module'          => $this->name(),
            'title'           => $title,
            'tree'            => $tree,
            'tree_names'      => $this->tree_service->titles(),
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdmin2Action(ServerRequestInterface $request): ResponseInterface
    {
        return redirect(route('module', [
            'module' => $this->name(),
            'action' => 'Admin2',
            'tree'   => Validator::parsedBody($request)->string('tree'),
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdmin2DeleteAction(ServerRequestInterface $request): ResponseInterface
    {
        $block_id = Validator::queryParams($request)->integer('block_id');

        DB::table('block_setting')->where('block_id', '=', $block_id)->delete();

        DB::table('block')->where('block_id', '=', $block_id)->delete();

        $url = route('module', [
            'module' => $this->name(),
            'action' => 'Admin2',
        ]);

        return redirect($url);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdmin2MoveDownAction(ServerRequestInterface $request): ResponseInterface
    {
        $block_id = Validator::queryParams($request)->integer('block_id');

        $block_order = DB::table('block')
            ->where('block_id', '=', $block_id)
            ->value('block_order');

        $swap_block = DB::table('block')
            ->where('module_name', '=', $this->name())
            ->where('block_order', '>', $block_order)
            ->orderBy('block_order')
            ->first();

        if ($block_order !== null && $swap_block !== null) {
            DB::table('block')
                ->where('block_id', '=', $block_id)
                ->update([
                    'block_order' => $swap_block->block_order,
                ]);

            DB::table('block')
                ->where('block_id', '=', $swap_block->block_id)
                ->update([
                    'block_order' => $block_order,
                ]);
        }

        return response();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdmin2MoveUpAction(ServerRequestInterface $request): ResponseInterface
    {
        $block_id = Validator::queryParams($request)->integer('block_id');

        $block_order = DB::table('block')
            ->where('block_id', '=', $block_id)
            ->value('block_order');

        $swap_block = DB::table('block')
            ->where('module_name', '=', $this->name())
            ->where('block_order', '<', $block_order)
            ->orderBy('block_order', 'desc')
            ->first();

        if ($block_order !== null && $swap_block !== null) {
            DB::table('block')
                ->where('block_id', '=', $block_id)
                ->update([
                    'block_order' => $swap_block->block_order,
                ]);

            DB::table('block')
                ->where('block_id', '=', $swap_block->block_id)
                ->update([
                    'block_order' => $block_order,
                ]);
        }

        return response();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getAdmin2EditAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        $block_id = Validator::queryParams($request)->integer('block_id', 0);

        if ($block_id === 0) {
            // Creating a new name badge
            $header      = '';
            $note        = '';
            $regex       = '';
            $snippet     = '';
            $access      = ''.Auth::PRIV_NONE; //managers
            $gedcom_id   = null;
            $block_order = 1 + (int) DB::table('block')->where('module_name', '=', $this->name())->max('block_order');

            $languages = [];

            $title = I18N::translate('Add a name badge');
        } else {
            // Editing an existing name badge
            $header      = $this->getBlockSetting($block_id, 'header');
            $note        = $this->getBlockSetting($block_id, 'note');
            $regex       = $this->getBlockSetting($block_id, 'regex');
            $snippet     = $this->getBlockSetting($block_id, 'snippet');
            $access      = (int)$this->getBlockSetting($block_id, 'access');
            $gedcom_id   = DB::table('block')->where('block_id', '=', $block_id)->value('gedcom_id');
            $block_order = DB::table('block')->where('block_id', '=', $block_id)->value('block_order');

            $title = I18N::translate('Edit the name badge');
        }

        $gedcom_ids = $this->tree_service->all()
            ->mapWithKeys(static function (Tree $tree): array {
                return [$tree->id() => $tree->title()];
            })
            ->all();

        $gedcom_ids = ['' => MoreI18N::xlate('All')] + $gedcom_ids;

        return $this->viewResponse($this->name() . '::admin/badges/edit', [
            'block_id'    => $block_id,
            'block_order' => $block_order,
            'header'      => $header,
            'note'        => $note,
            'regex'       => $regex,
            'snippet'     => $snippet,
            'access'      => $access,
            'title'       => $title,
            'gedcom_id'   => $gedcom_id,
            'gedcom_ids'  => $gedcom_ids,
            'module'      => $this->name(),
        ]);
    }

    public function setInitialNameBadges(bool $includePlaceholder): void {
        //apparently cannot route on boot(), static internal url  is anyway hacky
        /*
        $url = route('module', [
            'module' => $this->name(),
            'action' => 'Admin2',
        ]);
        */

        if ($includePlaceholder) {
           $this->setInitialNameBadge(
                'Placeholder [added by Vesta]',
                'An initial placeholder to promote this new feature - just delete it!',
                '/.*/',
                '<span class="wt-icon-help" title="Name badges are a new Vesta feature: Configure them in the module preferences of Vesta Classic Look & Feel!"><i class="fa-solid fa-question-circle fa-fw" aria-hidden="true"></i></span>',
                '0',
                null,
                1);

        } //else skip - probably better no to re-add this again & again in new setups

        $this->setInitialNameBadge(
            'Has FamilySearch ID [added by Vesta]',
            'Exemplary name badge. Change access level to display this. Image is the FamilySearch icon.',
            '/\n1 _FSFTID/',
            '<img width="16" src="https://edge.fscdn.org/assets/docs/fs_logo_favicon_sq.png" />',
            '-1',
            null,
            2);

        $this->setInitialNameBadge(
            'BIRT with SOUR [added by Vesta]',
            'Exemplary name badge. Change access level to display this. Note on regex: The negative lookahead is required to avoid matching \'2 SOUR\' of subsequent facts. Note on image: Taken from \'webtrees\resources\css\facts\BIRT.png\'',
            '/\n1 BIRT(?!\n1)(.(?!\n1))*\n2 SOUR/sm',
            '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAMAAADW3miqAAADAFBMVEVHcEwCAgEAAAABAQAAAAAAAAAAAABqXygMDBjv3SQBAADaug4FBAIAAAELCw61nRzVthPAsTwUEQUECiuchxjTsw3KrRPSsw7QtB/VtQvUtRbGqxnWtxPHqREAAAAFBAAAAB8AAAAAAAOkhwBdSxazmhBORA48OB5KSDvQsAfIrBjSsQKejTKRhUKPgC2aiS1OSjt3ah6XiDrIqwynlC3DpxCrkQaqkxnMrxjSsxDHqQwcIjmsmC6/ow2plzK3nyDSsxDauAnUtQ/OtCfauhTQshHSsgTgvwvHqxPevAbdugDQsAW0nBzLrQ+MeiDEqBDLrx24nxvJrRwAADTHrByikjnSsQVoWhLEpwu/pRkpLD3LrAV4bz6umCR+cz+vlhPQsAiwlxHXtwvjwhLIqxPPsA+6oBMFBQLWuRO1mAIAAP2cizESGkLIqhHFri3RsAVlaXnApyaJgVSqmUG2nRa3nRPMrgnWuSF7cT+cjkZ9byPVtAU6MQJST0DLryCbjULBoworM1jYtwzgvxGhkDdjbJ5VTymkkjfUtA6/piC/qCjatwHKsCkdGw/cuw7UtRO2nh6rkxW1nRnLrRDWuBvdvAywmRnKrA7WthAhJkFXVD7QshPRtR2eiBVQRAOwmR2+qDO6oyZYVDxHPQPXuRuPfieYjEehkDS9pimznjPfvxW4mwbauhCnlTmXhzGsliVwaUubii/HryvAqznZuxi5nAe1oTLDpg9XTybKsB9YTheynSrOsRxjVg3WtxTAphplYlDFqRTGqx7EqybOrwnTswnNsBdpYj3JriPdvRd0ajDErS/MrgvkxBuvnDtyZyjGqQ3IryaVhCQ9MQCwmSHFrCzBpx7hwRfHrR2rlix3aBfWuBnZuxi0mxWhjCK8oQywmyKqlCXkwg2ylwu4nhjYtwqnkh+OgDbOrga4nxaIdhvVuB3aug/oxQ9YUz2zmx+ulhdmWyGKeBixlw/LrhjlwQHkwQjfvAbbuQfNsRrFqh3QsQ/NrxLStBLZuhjfvxVz83RuAAAA9XRSTlMAHxgjIQcqAQMBL/URDROt7AIKDzDn0OXs8eTX9eEdBRYaBBUJDCQUQpOPnkYmIYxVKUvqs7s6MeTQVzeKtsaPxN/ZsfPs3fjc+d/IhOGc2e7asBvev+9az+1J72rPdpjkqeP7+uHFHBgeBnImbOmJHnJCnm1k0/dBiWj9PEO4UJ0z3ec3DzBpbrgwr/QzwfGcn7nV/dCi2PcsN/Lal0TBzLBAOtx6hp7jq+98une4unyf38e2tLmhTc5UwpxFrc1J7+b1rdb1denLjNvZ1MJPrr22LMbG4tPZpF69w6ClxLW97sXQ7MOc6dJ61NH+Xr98SW/Bxj96YdsAAAIhSURBVDjLY2BAA4KCnAw4ADtQDiTJDuLgUMWOYArrquAySV2nMFcZSCsZLRTLz8ChKDvv5w9NIN22QMLxmyZ2NWrF5aa24qUM6jYBEVu+GRdxYFNkWOnSVOdS1rtos/T2fdyBtYzCWBTx1vd9FXUS8IvatnPP9X8bFdlYlLCoajHv/t7MPX/Deofb/68dUORiRLdQEIj1Ws29fnj62x1yepsSw8rHwocZRrpdIjoMZm6i4gdvvJbxi/p4ay87ctCB1bRLzPA27TBrsBCIDkkOfP4wMsXNCt0yo57fvpMtp0/it/CQdol+Fvvk8X1JMRG0yLGfMm0C/8S5/GKu+6/KyL7nj//gc3FFlZAQiqIS96kOlnOWeLhyy0o/4pZNTXpzJ8BOg1kOKRQ4GVS1q/tnh+wO3STunpD6PfGT/YPYlRUKbMysDMiqVKxslu46dVpKKvnFS0+vV+/iYmbysLDJKaGFk+GO4+dkjgpc+eKb9jUsPFidiZlZTggjxM+HnrANkrh08+nntK/elxlYhXmxxMvaw2cunDy2Li7C/574163B2NOK898jzlKRy6zjBe4mfgsz4MWqSEtyzdmfy2ctDk9IClplbMDMg02Rsvb3PyaNfDXzVjtK/jLJYWPGllQYMrX0rZnY2NREfDr1NdK5mOWxOyuLkYsrnUFVr0CBkY1LEVeO4WFmAuvnZWRmYsWZ9ZRAaYiTg4OVlZ1hFNAKAABNKaFSPuL5kQAAAABJRU5ErkJggg==" />',
            '-1',
            null,
            3);

        $this->setInitialNameBadge(
            'BURI with OBJE (tombstone?) [added by Vesta]',
            'Exemplary name badge. Change access level to display this. Note on regex: The negative lookahead is required to avoid matching \'2 SOUR\' of subsequent facts. Note on image: Taken from \'webtrees\resources\css\facts\DEAT.png\'',
            '/\n1 BURI(?!\n1)(.(?!\n1))*\n2 OBJE/sm',
            '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAFrklEQVR4Ae2X3W8U1RvHZ87svOxOt93Sd378LCCltRgSgcQb4yVEEuKNIYYYLwzXXPkHyH9gYrz3yhu80niFMRAujIEQTRREqNCiltJ2p9vu7rzMmRk/Z7qTNBg23dULTPokT3JmzjnPfs/3+zzPnNW1F8z+BujUqRPqXabGlUolnxdC10yzpAthZPlcFwuCUB8YcBmxrg97sRmanv5/1TQt68GDhcaBAwed+flZN00TA7cty3STRK6zbBnXrl79tmBSdDx/PnPm9LBlWbXV1adrPHt9ATp//p08WL1efzOOowtB0F4WQmhJko6qNaVtm3Fd9yueP8Y13/ePh2H4VhRFo8wpwHYcy4xxO47jl2u12jcs+6RXQKUclb59uoGBAaPR8E5mWRaZptkWIjkKsMi27QVd19uVimsWG7e2mser1cEL7K2naerB4uEs01rsvU6okXK5cqhYW6lU8/i7BvT06Ur+YNtOHYZ+TZKkQXLG1Wp1anR09PM01a8vLS2FnrcVLi4uTqyursn5+bkfpqcPfjg2NrqwsPAo8Lz1D2DrdU1LUczRABaeO/du+eLF9wYcx2kRvr1rQADJ0ZM/CZUUQXsAMxmR1wBz7cqVL75negIXuMucfufOLwv4T0WgkydfaxqGdhiZW2EYDCq/ceNaWRHEAWVPgFS+KLMsO4J+SYBBZAKc8FdWVpRMw4wrANwkbzZSjDaQkDOFFEapZLR4V5dSrjMdkAjSAGHHsp5yqCg2GPKhWgEoA+oPApM/VsyzAtoKgqDOWOIaYHbGSQALA2kMeTJNswxgyfr6qqXA4npPgIqTIJUmMIaSsUHCmuRFyrNyiSfPCwTgJlsyDuJTAKFpGg4HFGqK596qrN3O5aXMtQSGNg1DDBN4H+Nqq9UqZNHx51YLObNkmq7ktzPA+Sw16Nh6X2XveV7+sLn5e2tiYl+rVDLH6CV1ElTjdOZuurphKHYNl2U2HpGZATJGTBX7e5eM3qMkU1IF9JRl3h+kGYp8XXdAABcGewOGzXLZkVmWjt++fWtEPcO06AmQyh1ljmMbyqhSP4rCiIrSaZZ2IVc3UMRoRVEcInXKwXwa5X7SR7WKdYD2xlBhFEbKZnUgVeYNmFKA8ik87c6QluIlzjNEIVRpISGdPFZzBDR4t6tuXdpZwhsbW3JkZMjmlAegXKKeIEHDIlA3hih3Qb6xRzTZj2T5N1AFNgCkfmf3gIqyRHsNqhWAiCSVBDb5hJi7aW7bShkmxZBZGJWqN5t+3AHUm2Q3b97Kd9RqE3Jycrit5AJmmx8pg9VRQfGuBsuWgKPtmLrDqE0fijrMpH3l0NTUGGQJHbm2YN3T9QymDKtTul0zs5M/gDKGpIx1unWbCk3VFNZfUnteW46NDSqpHFiqqt6CicKRtFsOBcjtcJgqe22lIqCUZKpKpJK8Z0BPnvwWz8xMCpKxxikbKUYcX+U7bvp+EHeJpRppoBIbF4BBxjArkrkvhjoJ7vPV/tP3s/swNed59RNzczP53WhkZOQeS77DtYcPH5X37596g5vjIJ+eNaR6FWWGpZQuMWiyEhCiAJP2DUjtp2ccgfYI+ofCUJ6r1YZO0zSnkeSzAtC9e/ezycmJ92HzEDeCu6x9SbFDHimWhnh2fb9lFLcD3vUHiNvdInehH/me/dxoNB5TP1OOY2YEXKSaG2fPvl1mmR8E8f/4eD4kd1YpgCeUe4NccWq14bsbGxurXPpi17Vd1oa4DtD+AB05cvTL2dlXvr506VJrfv7YGKesAU7dAsquWxGXL380waUtOXZsPpqdPfppqSSypaXHNlfYSQBXlpfXl7nu6uPj4xXYbqvcQ3r/3/pfVuuATnGj45aB8SMrz1xLh3Grs9YspEJ6R92VGHp49k8BPdvyRWdcNDvZkVlwmxRFzB1x0x1rk//CP9c9QHuA9gDtAfoLf+7XrvozOYsAAAAASUVORK5CYII=" />',
            '-1',
            null,
            4);
    }

    public function setInitialNameBadge(
        $header,
        $note,
        $regex,
        $snippet,
        $access,
        $gedcom_id,
        $block_order): void {

        DB::table('block')->insert([
            'gedcom_id'   => null,
            'module_name' => $this->name(),
            'block_order' => $block_order,
        ]);

        $block_id = (int) DB::connection()->getPdo()->lastInsertId();

        $this->setBlockSetting($block_id, 'header', $header);
        $this->setBlockSetting($block_id, 'note', $note);
        $this->setBlockSetting($block_id, 'regex', $regex);
        $this->setBlockSetting($block_id, 'snippet', $snippet);
        $this->setBlockSetting($block_id, 'access', $access);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function postAdmin2EditAction(ServerRequestInterface $request): ResponseInterface
    {
        $block_id    = Validator::queryParams($request)->integer('block_id', 0);
        $header      = Validator::parsedBody($request)->string('header');
        $note        = Validator::parsedBody($request)->string('note');
        $regex       = Validator::parsedBody($request)->string('regex');
        $snippet     = Validator::parsedBody($request)->string('snippet');
        $access      = Validator::parsedBody($request)->string('access');
        $gedcom_id   = Validator::parsedBody($request)->string('gedcom_id');
        $block_order = Validator::parsedBody($request)->integer('block_order');

        if ($gedcom_id === '') {
            $gedcom_id = null;
        }

        $header  = $this->html_service->sanitize($header);
        $note    = $this->html_service->sanitize($note);
        $regex   = $this->html_service->sanitize($regex);

        //this is too restrictive apparently
        //$snippet = $this->html_service->sanitize($snippet);

        if ($block_id !== 0) {
            DB::table('block')
                ->where('block_id', '=', $block_id)
                ->update([
                    'gedcom_id'   => $gedcom_id,
                    'block_order' => $block_order,
                ]);
        } else {
            DB::table('block')->insert([
                'gedcom_id'   => $gedcom_id,
                'module_name' => $this->name(),
                'block_order' => $block_order,
            ]);

            $block_id = (int) DB::connection()->getPdo()->lastInsertId();
        }

        $this->setBlockSetting($block_id, 'header', $header);
        $this->setBlockSetting($block_id, 'note', $note);
        $this->setBlockSetting($block_id, 'regex', $regex);
        $this->setBlockSetting($block_id, 'snippet', $snippet);
        $this->setBlockSetting($block_id, 'access', $access);

        $url = route('module', [
            'module' => $this->name(),
            'action' => 'Admin2',
        ]);

        return redirect($url);
    }

    public function addBadges(Tree $tree, string $gedcom): string {

        $full = '';

        //note: 'negative' matches are problematic wrt fake INDI records used e.g. in individual-page-name.phtml
        //therefore just exclude fake INDI records altogether!
        //
        if (preg_match('@xref@', $gedcom, $match)) {
            return $full;
        }

        $nameBadges = $this->nameBadgesForTree($tree);

        foreach ($nameBadges as $nameBadge) {
            if ($nameBadge->access >= Auth::accessLevel($tree)) {
                try {
                    if (preg_match($nameBadge->regex, $gedcom, $match)) {
                        $full .= $nameBadge->snippet;
                    }
                } catch (\Exception $e) {
                    error_log("[Vesta Classic Look & Feel] error in name badge regex, check your module settings: " . $nameBadge->regex);
                }
            }
        }

        return $full;
    }

    /**
     * @param Tree $tree
     *
     * @return Collection<int,object>
     */
    public function nameBadgesForTree(Tree $tree): Collection
    {
        return DB::table('block')
            ->join('block_setting AS bs1', 'bs1.block_id', '=', 'block.block_id')
            ->join('block_setting AS bs2', 'bs2.block_id', '=', 'block.block_id')
            ->join('block_setting AS bs3', 'bs3.block_id', '=', 'block.block_id')
            ->join('block_setting AS bs4', 'bs4.block_id', '=', 'block.block_id')
            ->join('block_setting AS bs5', 'bs5.block_id', '=', 'block.block_id')
            ->where('module_name', '=', $this->name())
            ->where('bs1.setting_name', '=', 'header')
            ->where('bs2.setting_name', '=', 'note')
            ->where('bs3.setting_name', '=', 'regex')
            ->where('bs4.setting_name', '=', 'snippet')
            ->where('bs5.setting_name', '=', 'access')
            ->where(static function (Builder $query) use ($tree): void {
                $query
                    ->whereNull('gedcom_id')
                    ->orWhere('gedcom_id', '=', $tree->id());
            })
            ->orderBy('block_order')
            ->select(['block.block_id', 'block_order', 'gedcom_id', 'bs1.setting_value AS header', 'bs2.setting_value AS note', 'bs3.setting_value AS regex', 'bs4.setting_value AS snippet', 'bs5.setting_value AS access'])
            ->get()
            ->map(static function (object $row): object {
                $row->block_id    = (int) $row->block_id;
                $row->block_order = (int) $row->block_order;
                $row->gedcom_id   = (int) $row->gedcom_id;

                return $row;
            });
    }
}
