<?php

namespace A909M\FilamentStateFusion\Actions;

use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\HasName;
use Illuminate\Support\Str;

class StateFusionActionGroup extends ActionGroup
{
    use HasName;

    public $stateClass = null;

    public function __construct(?string $name, ?string $stateClass = null)
    {
        $this->name($name);
        $this->stateClass($stateClass);
        $this->actions($this->generateStateTransitionActions($stateClass, $name));
    }

    protected function setUp(): void
    {
        parent::setUp();

        // $this->actions($this->generateStateTransitionActions($this->getStateClass(), $this->getName()));
    }

    public static function generate(string $columnName, string $stateClass): static
    {
        $static = app(static::class, [
            'name' => $columnName,
            'stateClass' => $stateClass,
        ]);
        $static->configure();

        return $static;
    }

    protected function generateStateTransitionActions(string $stateClass, string $name): array
    {
        // Get all state classes
        $stateClasses = $stateClass::all();

        $actions = [];

        foreach ($stateClasses as $stateClass) {
            $state = new $stateClass(null);

            $actions[] = StateFusionAction::make(Str::slug($state::getMorphClass()))
                ->attribute($name)
                ->transitionTo($state);
        }

        return $actions;
    }

    public function stateClass(string $stateClass): static
    {
        $this->stateClass = $stateClass;

        return $this;
    }

    public function getStateClass()
    {
        return $this->stateClass;
    }
}
