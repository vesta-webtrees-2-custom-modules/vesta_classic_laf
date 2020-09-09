<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Aura\Router\Route;
use Cissee\Webtrees\Module\ClassicLAF\Factories\CustomXrefFactory;
use Cissee\WebtreesExt\CustomIndividualFactory;
use Cissee\WebtreesExt\IndividualNameHandler;
use DOMDocument;
use DOMXPath;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Factory;
use Fisharebest\Webtrees\Http\Middleware\AuthEditor;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaModuleTrait;
use function app;
use function route;

class ClassicLAFModule extends AbstractModule implements 
  ModuleCustomInterface, 
  ModuleConfigInterface,
  ModuleGlobalInterface,
  MiddlewareInterface {

  use ModuleCustomTrait, ModuleConfigTrait, ModuleGlobalTrait, VestaModuleTrait {
    VestaModuleTrait::customTranslations insteadof ModuleCustomTrait;
    VestaModuleTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
    VestaModuleTrait::getAssetAction insteadof ModuleCustomTrait;
    VestaModuleTrait::assetUrl insteadof ModuleCustomTrait;
    
    VestaModuleTrait::getConfigLink insteadof ModuleConfigTrait;
  }

  use ClassicLAFModuleTrait;

  public function customModuleAuthorName(): string {
    return 'Richard Cissée';
  }

  public function customModuleVersion(): string {
    return file_get_contents(__DIR__ . '/latest-version.txt');
  }

  public function customModuleLatestVersionUrl(): string {
    return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_classic_laf/master/latest-version.txt';
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
    
    if ($compactIndividualPage || !$cropThumbnails) {
      // Replace an existing view with our own version.
      View::registerCustomView('::individual-page', $this->name() . '::individual-page');      
    }
    
    if ($compactIndividualPage) {
      View::registerCustomView('::individual-name', $this->name() . '::individual-name');      
    }
    
    if (!$cropThumbnails) {
      View::registerCustomView('::chart-box', $this->name() . '::chart-box');
      View::registerCustomView('::selects/individual', $this->name() . '::selects/individual');
      View::registerCustomView('::selects/media', $this->name() . '::selects/media');
    }

    $nickBeforeSurn = boolval($this->getPreference('NICK_BEFORE_SURN', '1'));

    $handler = app(IndividualNameHandler::class);
    $handler->setNickBeforeSurn($nickBeforeSurn);
    //must explicitly register in order to re-use elsewhere! (see webtrees #3085)
    app()->instance(IndividualNameHandler::class, $handler);

    $cache = app('cache.array');
    Factory::individual(new CustomIndividualFactory($cache, $compactIndividualPage, $cropThumbnails));

    $customPrefixes = boolval($this->getPreference('CUSTOM_PREFIXES', '0'));
    if ($customPrefixes) {
      Factory::xref(new CustomXrefFactory($this));
    }
    
    $this->flashWhatsNew('\Cissee\Webtrees\Module\ClassicLAF\WhatsNew', 3);
  }
  
  public function headContent(): string {
      return '<link rel="stylesheet" href="'.$this->assetUrl('css/theme.css').'">';
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
  
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    
    /*
    if ($route->handler === EditFact::class) {
      //TODO requires WrapHandler update! Otherwise no effect.
      //discussed in https://github.com/fisharebest/webtrees/issues/3339
      $route->handler(EditFactAdjusted::class); //note - we could use a single wrapper class for all targets here, no need to have one per target
      
      //not even required, $request is mutable
      //$request = $request->withAttribute('route', $route);
    }
    //seems easier, but certainly less elegant, to strip header and footer from generated html
    */
    
    $strippedEdit = boolval($this->getPreference('COMPACT_EDIT', '1'));
    
    $response = $handler->handle($request);
    
    if ($strippedEdit && ($response->getStatusCode() === StatusCodeInterface::STATUS_OK)) {
      $route = $request->getAttribute('route');
      assert($route instanceof Route);
      $route_middleware = $route->extras['middleware'] ?? [];

      $strip = false;
      foreach ($route_middleware as $middleware) {
        //all edit routes have this middleware, see WebRoutes.php
        //$middleware is a string, not an instance of AuthEditor!
        if ($middleware === AuthEditor::class) {
          $strip = true;
          break;
        }
      }
      
      if ($strip) {
        //must not adjust json responses
        $contentType = $response->getHeaderLine("Content-Type");
        if (substr($contentType, 0, strlen("text/html")) === "text/html") {
          $html = $response->getBody()->__toString();
          $content = self::strippedLayout($html);
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
  
  public static function strippedLayout(string $html): string {
    $dom=new DOMDocument();
    $dom->validateOnParse = false;
    $internalErrors = libxml_use_internal_errors(true);
    
    //have to prefix to force utf-8 (relevant e.g. for modals)
    //cf https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
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

    //adjust container to allow additional css styling
    $nodes = $xpath->query('//div[@class = "container wt-main-container"]');

    if (!empty($nodes)) {
      foreach ($nodes as $node) {
        $node->setAttribute("class", "container edit-container wt-main-container");
      }
    }
    
    //TODO: only keep this after next webtrees release (2.0.8)
    //WEBTREES-DEV
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
