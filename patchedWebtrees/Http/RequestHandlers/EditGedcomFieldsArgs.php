<?php

namespace Cissee\WebtreesExt\Http\RequestHandlers;


class EditGedcomFieldsArgs {
    
    protected bool $canConfigure;
    
    public function canConfigure(): bool {
        return $this->canConfigure;
    }
    
    public function __construct(
        bool $canConfigure = false) {
        
        $this->canConfigure = $canConfigure;
    }
}
