<?php

namespace A909M\FilamentStateFusion;

use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Generates Filament tabs for filtering records based on their state.
 *
 * This class provides a fluent API for creating tabs that filter Eloquent models
 * by their state attributes, with support for badges, icons, and custom styling.
 */
class StateFusionTabs
{
    /** @var Model|string The Eloquent model class or instance to generate tabs for */
    protected Model | string $model;

    /** @var string|null The state attribute name to filter by */
    protected ?string $attribute = null;

    /** @var bool Whether to include badges showing record counts */
    protected bool $includeBadge = true;

    /** @var bool Whether to include an "All" tab showing all records */
    protected bool $includeAll = false;

    /** @var array<int, Tab>|null Cached generated tabs */
    protected ?array $tabs = null;

    /**
     * Create a new StateFusionTabs instance.
     *
     * @param  Model|string  $model  The Eloquent model class or instance
     */
    public function __construct(Model | string $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new StateFusionTabs instance using the static factory method.
     *
     * @param  Model|string  $model  The Eloquent model class or instance
     */
    public static function make(Model | string $model): self
    {
        return new self($model);
    }

    /**
     * Set the state attribute to filter by.
     *
     * If not specified, the first attribute from the model's default states will be used.
     *
     * @param  string  $attribute  The attribute name containing the state
     */
    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Configure whether to include badges showing record counts on tabs.
     *
     * @param  bool  $includeBadge  Whether to show badges with counts
     */
    public function badge(bool $includeBadge = true): self
    {
        $this->includeBadge = $includeBadge;

        return $this;
    }

    /**
     * Configure whether to include an "All" tab showing all records.
     *
     * @param  bool  $includeAll  Whether to include the "All" tab
     */
    public function includeAll(bool $includeAll = true): self
    {
        $this->includeAll = $includeAll;

        return $this;
    }

    /**
     * Get the state attribute name to use for filtering.
     *
     * Returns the explicitly set attribute or the first attribute from the model's default states.
     *
     * @return string The attribute name
     */
    public function getAttribute(): string
    {
        return $this->attribute ?? array_key_first(
            $this->model::getDefaultStates()->toArray()
        );
    }

    /**
     * Generate the tabs based on the model's states.
     *
     * Creates tabs for each state with optional badges, icons, and query modifications.
     *
     * @return array<int, Tab> Array of generated Tab instances
     */
    protected function generateTabs(): array
    {

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
        $states = $this->getAbstractStateClass()::all();

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

    /**
     * Get the abstract state class for the model's state attribute.
     *
     * Resolves the model instance and retrieves the cast class for the state attribute.
     *
     * @return string The fully qualified class name of the state class
     */
    protected function getAbstractStateClass(): string
    {
        $modelInstance = app($this->model);

        return $modelInstance->getCasts()[$this->getAttribute()];
    }

    /**
     * Convert the tabs to an array.
     *
     * Generates and returns the tabs as an array of Tab instances.
     *
     * @return array<int, Tab> Array of generated Tab instances
     */
    public function toArray(): array
    {
        return $this->generateTabs();
    }
}
