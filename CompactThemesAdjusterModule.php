<?php

namespace CompactThemes;

use Fisharebest\Webtrees\Carbon;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Str;
use function response;
use function route;
use function view;

class CompactThemesAdjusterModule extends AbstractModule implements ModuleCustomInterface, ModuleGlobalInterface {
  
  use ModuleCustomTrait;
  use ModuleGlobalTrait;
  
  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return '2.0.0.1';
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://cissee.de';
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
}
