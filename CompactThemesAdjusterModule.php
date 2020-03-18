<?php

namespace CompactThemes;

use Cissee\WebtreesExt\CustomIndividualFactory;
use Cissee\WebtreesExt\CustomTreeService;
use Cissee\WebtreesExt\IndividualNameHandler;
use Fisharebest\Webtrees\Carbon;
use Fisharebest\Webtrees\Contracts\IndividualFactoryInterface;
use Fisharebest\Webtrees\Http\RequestHandlers\ControlPanel;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\View;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vesta\ControlPanel\ControlPanelUtils;
use Vesta\ControlPanel\Model\ControlPanelCheckbox;
use Vesta\ControlPanel\Model\ControlPanelPreferences;
use Vesta\ControlPanel\Model\ControlPanelSection;
use Vesta\ControlPanel\Model\ControlPanelSubsection;
use function app;
use function redirect;
use function response;
use function route;
use function view;

class CompactThemesAdjusterModule extends AbstractModule implements ModuleCustomInterface, ModuleConfigInterface, ModuleGlobalInterface {
    
  use VestaModuleCustomTrait {
    VestaModuleCustomTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
  }
  use ModuleCustomTrait;
  use ModuleGlobalTrait;
  
  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return file_get_contents(__DIR__ . '/latest-version.txt');
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://raw.githubusercontent.com/ric2016/compact_themes_adjuster/master/latest-version.txt';
  }

  public function customModuleSupportUrl(): string {
    return 'https://cissee.de';
  }

  public function title(): string {
    return I18N::translate('Compact Themes Adjuster');
  }

  public function description(): string {
    return I18N::translate('A module adjusting the themes, providing a more compact layout  similar to the webtrees 1.x version.');
  }

  /**
   * Where does this module store its resources
   *
   * @return string
   */
  public function resourcesFolder(): string {
    return __DIR__ . '/resources/';
  }

  /**
   * Additional/updated translations.
   *
   * @param string $language
   *
   * @return string[]
   */
  public function customTranslations(string $language): array {
    //TODO
    return [];
  }
  
  /**
   * Bootstrap the module
   */
  public function boot(): void
  {
      // Register a namespace for our views.
      View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');

      // Replace an existing view with our own version.
      View::registerCustomView('::individual-page', $this->name() . '::individual-page');
      
      $nickBeforeSurn = boolval($this->getPreference('NICK_BEFORE_SURN', '1'));
      
      $handler = app(IndividualNameHandler::class);
      $handler->setNickBeforeSurn($nickBeforeSurn);
      //must explicitly register in order to re-use elsewhere! (see webtrees #3085)
      app()->instance(IndividualNameHandler::class, $handler);
      
      //TODO - requires the develop-branch
      //once this is fixed, adjust text in module config!
      //$cache = app('cache.array');
      //app()->instance(IndividualFactoryInterface::class, new CustomIndividualFactory($cache));
      
      //experimental, could be used to redefine tree e.g. for xref handling as in webtrees 1.x
      //see https://www.webtrees.net/index.php/en/forum/help-for-2-0/33978-identities-in-gedcom-file#74475
      //app()->instance(TreeService::class, new CustomTreeService());
  }
  
  public function headContent(): string
  {
      return '<link rel="stylesheet" href="'.$this->assetUrl('css/theme.css').'">';
  }
  
  public function assetsViaViews(): array {
    return [
        'css/theme.css' => 'css/theme'];
  }

  //adapted from ModuleCustomTrait

  /**
   * Create a URL for an asset.
   *
   * @param string $asset e.g. "css/theme.css" or "img/banner.png"
   *
   * @return string
   */
  public function assetUrl(string $asset): string {
    $assetFile = $asset;
    $assetsViaViews = $this->assetsViaViews();
    if (array_key_exists($asset, $assetsViaViews)) {
      $assetFile = 'views/' . $assetsViaViews[$asset] . '.phtml';
    }

    $file = $this->resourcesFolder() . $assetFile;

    // Add the file's modification time to the URL, so we can set long expiry cache headers.
    //[RC] assume this is also ok for views (i.e. assume the rendered content isn't dynamic)
    $hash = filemtime($file);

    return route('module', [
        'module' => $this->name(),
        'action' => 'asset',
        'asset' => $asset,
        'hash' => $hash,
    ]);
  }

  //adapted from ModuleCustomTrait

  /**
   * Serve a CSS/JS file.
   *
   * @param ServerRequestInterface $request
   *
   * @return ResponseInterface
   */
  public function getAssetAction(ServerRequestInterface $request): ResponseInterface {
    // The file being requested.  e.g. "css/theme.css"
    $asset = $request->getQueryParams()['asset'];

    // Do not allow requests that try to access parent folders.
    if (Str::contains($asset, '..')) {
      throw new AccessDeniedHttpException($asset);
    }

    $assetsViaViews = $this->assetsViaViews();
    if (array_key_exists($asset, $assetsViaViews)) {
      $assetFile = $assetsViaViews[$asset];
      $assertRouter = function (string $asset) {
        return $this->assetUrl($asset);
      };
      $content = view($this->name() . '::' . $assetFile, ['assetRouter' => $assertRouter]);
    } else {
      $file = $this->resourcesFolder() . $asset;

      if (!file_exists($file)) {
        throw new NotFoundHttpException($file);
      }

      $content = file_get_contents($file);
    }

    $expiry_date = Carbon::now()->addYears(10)->toDateTimeString();

    $extension = pathinfo($asset, PATHINFO_EXTENSION);

    $mime_types = [
        'css' => 'text/css',
        'gif' => 'image/gif',
        'js' => 'application/javascript',
        'jpg' => 'image/jpg',
        'jpeg' => 'image/jpg',
        'json' => 'application/json',
        'png' => 'image/png',
        'txt' => 'text/plain',
    ];

    $mime_type = $mime_types[$extension] ?? 'application/octet-stream';

    $headers = [
        'Content-Type' => $mime_type,
        'Expires' => $expiry_date,
    ];
    return response($content, 200, $headers);
  }
    
  public function getConfigLink(): string {
    return route('module', [
        'module' => $this->name(),
        'action' => 'Admin',
    ]);
  }

  public function getAdminAction(ServerRequestInterface $request): ResponseInterface {
    return response($this->editConfig());
  }

  public function postAdminAction(ServerRequestInterface $request): ResponseInterface {
    $this->saveConfig($request);

    $url = route('module', [
        'module' => $this->name(),
        'action' => 'Admin',
    ]);

    return redirect($url);
  }
  
  protected function editConfig() {
    ob_start();
    $utils = new ControlPanelUtils($this);
    $utils->printPrefs($this->createPrefs(), $this->name());
    $prefs = ob_get_clean();

    $innerHtml = "";
    $innerHtml .= view('components/breadcrumbs', ['links' => [route(ControlPanel::class) => I18N::translate('Control panel'), route('modules') => I18N::translate('Modules'), $this->title()]]);
    $innerHtml .= "<h1>" . $this->title() . "</h1>";
    $innerHtml .= "<p class=\"text-muted\">" . $this->description() . "</p>";
    $innerHtml .= "<p class=\"text-muted\">" . I18n::translate("Also includes further adjustments which allow to preserve some webtrees 1.x functionality.") . "</p>";
    $innerHtml .= $prefs;

    // Insert the view into the (main) layout
    $html = View::make('layouts/administration', [
                'title' => $this->title(),
                'content' => $innerHtml
    ]);

    return $html;
  }
  
  protected function saveConfig(ServerRequestInterface $request) {
    $utils = new ControlPanelUtils($this);
    $utils->savePostData($request, $this->createPrefs());
  }
  
  protected function createPrefs() {
    
    $sub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Nicknames'),
            array(new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Display nicknames before surnames - Caution: Functionality is currently broken due to ongoing changes in webtrees core code.'),
                /* I18N: Module Configuration */I18N::translate('Handle nicknames as in webtrees 1.x, instead of appending them to the name.') . ' ' .
                /* I18N: Module Configuration */I18N::translate('Note that this doesn\'t affect GEDCOM name fields that already include a nickname, i.e. you may always position the nickname explicitly for specific names.'),
                'NICK_BEFORE_SURN',
                '1')));
    
    $sections = array();
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('Individuals'),
            '',
            $sub);
    
    return new ControlPanelPreferences($sections);
  }
}
