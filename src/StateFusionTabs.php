<?php

namespace A909M\StateFusion;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StateFusionTabs
{
    protected Model | string $model;

    protected ?string $attribute = null;

    protected bool $includeBadge = true;

    protected bool $includeAll = false;

    protected ?array $tabs = null;

    public function __construct(Model | string $model)
    {
        $this->model = $model;
    }

    public static function make(Model | string $model): self
    {
        return new self($model);
    }

    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function badge(bool $includeBadge = true): self
    {
        $this->includeBadge = $includeBadge;

        return $this;
    }

    public function includeAll(bool $includeAll = true): self
    {
        $this->includeAll = $includeAll;

        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute ?? array_key_first(
            $this->model::getDefaultStates()->toArray()
        );
    }

    protected function generateTabs(): array
    {

        $modelInstance = app($this->model);
        $abstractState = $modelInstance->getCasts()[$this->getAttribute()];

        $tabs = [];

        // Add "All" tab if requested
        if ($this->includeAll) {
            $allTab = Tab::make()
                ->label('All');

            if ($this->includeBadge) {
                $allTab->badge($this->model::query()->count());
            }

            $tabs[] = $allTab;
        }

        // Generate state-specific tabs
        $states = $abstractState::all();
        foreach ($states as $key => $value) {
            $state = new $value(null);

            $tab = Tab::make($key)
                ->label($state->getLabel())
                ->icon($state->getIcon())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereState($this->getAttribute(), $value::getMorphClass()));

            if ($this->includeBadge) {
                $tab->badgeColor($state->getColor())
                    ->badgeTooltip($state->getDescription())
                    ->badge($this->model::query()->whereState($this->getAttribute(), $value::getMorphClass())->count());
            }

            $tabs[] = $tab;
        }

        $this->tabs = $tabs;

        return $tabs;
    }

    public function toArray(): array
    {
        return $this->generateTabs();
    }
}
