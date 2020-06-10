<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Aura\Router\Route;
use Cissee\WebtreesExt\CustomIndividualFactory;
use Cissee\WebtreesExt\CustomTreeService;
use Cissee\WebtreesExt\Http\RequestHandlers\EditFactAdjusted;
use Cissee\WebtreesExt\IndividualNameHandler;
use Fisharebest\Webtrees\Factory;
use Fisharebest\Webtrees\Http\RequestHandlers\EditFact;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vesta\VestaModuleTrait;
use function app;
use function route;

class ClassicLAFModule extends AbstractModule implements 
  ModuleCustomInterface, 
  ModuleConfigInterface,
  ModuleGlobalInterface/*,
  MiddlewareInterface*/ {

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

    // basic layout for edit dialogs
    View::registerCustomView('::layouts/stripped', $this->name() . '::layouts/stripped');
    
    // Replace an existing view with our own version.
    View::registerCustomView('::individual-page', $this->name() . '::individual-page');

    $nickBeforeSurn = boolval($this->getPreference('NICK_BEFORE_SURN', '1'));

    $handler = app(IndividualNameHandler::class);
    $handler->setNickBeforeSurn($nickBeforeSurn);
    //must explicitly register in order to re-use elsewhere! (see webtrees #3085)
    app()->instance(IndividualNameHandler::class, $handler);

    $cache = app('cache.array');
    Factory::individual(new CustomIndividualFactory($cache));

    $customPrefixes = boolval($this->getPreference('CUSTOM_PREFIXES', '0'));
    app()->instance(TreeService::class, new CustomTreeService($customPrefixes?$this:null));      
    
    $this->flashWhatsNew('\Cissee\Webtrees\Module\ClassicLAF\WhatsNew', 2);
  }
  
  public function headContent(): string {
      return '<link rel="stylesheet" href="'.$this->assetUrl('css/theme.css').'">';
  }
  
  public function assetsViaViews(): array {
    return [
        'css/theme.css' => 'css/theme'];
  }
    
  public function getConfigLink(): string {
    return route('module', [
        'module' => $this->name(),
        'action' => 'Admin',
    ]);
  }
  
  /*
  public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    $route = $request->getAttribute('route');
    assert($route instanceof Route);
    
    //TODO lots of others
    if ($route->handler === EditFact::class) {
      //TODO requires WrapHandler update! Otherwise no effect.
      //discussed in https://github.com/fisharebest/webtrees/issues/3339
      $route->handler(EditFactAdjusted::class);
      
      //not even required, $request is mutable
      //$request = $request->withAttribute('route', $route);
    }
    
    // Generate the response.
    return $handler->handle($request);
  }
  */
}
