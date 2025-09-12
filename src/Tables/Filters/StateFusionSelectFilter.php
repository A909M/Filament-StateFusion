<?php

namespace A909M\FilamentStateFusion\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StateFusionSelectFilter extends SelectFilter
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->options(fn (Table $table) => (new ($table->getModel())())->getCasts()[$this->getAttribute()]::getStatesLabel($table->getModel()));
    }
}
