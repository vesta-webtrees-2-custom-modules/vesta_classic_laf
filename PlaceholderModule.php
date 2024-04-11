<?php

namespace Cissee\Webtrees\Module\ClassicLAF;

use Cissee\WebtreesExt\AbstractModule;
use Cissee\WebtreesExt\Module\ModuleMetaInterface;
use Cissee\WebtreesExt\Module\ModuleMetaTrait;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Webtrees;
use Vesta\CommonI18N;
use Vesta\VestaModuleTrait;

class PlaceholderModule extends AbstractModule implements
    ModuleCustomInterface,
    ModuleMetaInterface {

    use ModuleCustomTrait, ModuleMetaTrait, VestaModuleTrait {
        VestaModuleTrait::customTranslations insteadof ModuleCustomTrait;
        VestaModuleTrait::getAssetAction insteadof ModuleCustomTrait;
        VestaModuleTrait::assetUrl insteadof ModuleCustomTrait;
        ModuleMetaTrait::customModuleVersion insteadof ModuleCustomTrait;
        ModuleMetaTrait::customModuleLatestVersion insteadof ModuleCustomTrait;
    }

    ////////

    use ClassicLAFModuleTrait;

    public function customModuleLatestMetaDatasJsonUrl(): string {
        return 'https://raw.githubusercontent.com/vesta-webtrees-2-custom-modules/vesta_classic_laf/master/metadata.json';
    }

    ////////

    public function customModuleMetaDatasJson(): string {
        return file_get_contents(__DIR__ . '/metadata.json');
    }

    public function customModuleAuthorName(): string {
        return 'Richard Cissée';
    }

    public function customModuleSupportUrl(): string {
        return 'https://cissee.de';
    }

    public function description(): string {
        $min_version = $this->minRequiredWebtreesVersion();

        //min version check
        $version_ok = version_compare(Webtrees::VERSION, $min_version) >= 0;
        if (!$version_ok) {
            return CommonI18N::noopModuleMin($min_version);
        }

        $max_version = $this->minUnsupportedWebtreesVersion();

        //max version check (allow current dev version though)
        $version_ok = (Webtrees::VERSION === $max_version.'-dev') || (version_compare($max_version, Webtrees::VERSION) > 0);
        if (!$version_ok) {
            return CommonI18N::noopModuleMax($max_version);
        }

        return '';
    }

    public function ifIncompatible(): ?PlaceholderModule {
        $min_version = $this->minRequiredWebtreesVersion();

        //min version check
        $version_ok = version_compare(Webtrees::VERSION, $min_version) >= 0;
        if (!$version_ok) {
            return $this;
        }

        $max_version = $this->minUnsupportedWebtreesVersion();

        //max version check (allow current dev version though)
        $version_ok = (Webtrees::VERSION === $max_version.'-dev') || (version_compare($max_version, Webtrees::VERSION) > 0);
        if (!$version_ok) {
            return $this;
        }

        return null;
    }

    public function boot(): void {
        //flash, but only once per day
        $title = $this->title();

        $cache = Registry::cache()->file();
        $key = $title . '-placeholder-flash';
        $cache->remember($key, function () use ($title) {
            FlashMessages::addMessage(CommonI18N::noopModuleMessage($title));
        }, 24*3600);
    }
}
