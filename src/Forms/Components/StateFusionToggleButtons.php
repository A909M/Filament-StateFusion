<?php

namespace A909M\FilamentStateFusion\Forms\Components;

use A909M\FilamentStateFusion\Concerns\HasStateAttributes;
use A909M\FilamentStateFusion\Contracts\HasStateAttributesContract;
use Filament\Forms\Components\ToggleButtons;

class StateFusionToggleButtons extends ToggleButtons implements HasStateAttributesContract
{
    use HasStateAttributes;

    protected function setUp(): void
    {
        parent::setUp();
        $this->options(fn ($model) => (new $model)->getCasts()[$this->getAttribute()]::getStatesLabel($model));
        $this->default(fn ($model) => $model::getDefaultStateFor($this->getAttribute()));
        $this->icons(fn ($model) => (new $model)->getCasts()[$this->getAttribute()]::getStatesIcon($model));
        $this->colors(fn ($model) => (new $model)->getCasts()[$this->getAttribute()]::getStatesColor($model));
    }
}
