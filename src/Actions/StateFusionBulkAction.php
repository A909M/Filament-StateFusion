<?php

namespace A909M\FilamentStateFusion\Actions;

use A909M\FilamentStateFusion\Concerns\HasStateAttributes;
use A909M\FilamentStateFusion\Concerns\InteractsWithStateAction;
use A909M\FilamentStateFusion\Concerns\ResolvesActionAttributes;
use A909M\FilamentStateFusion\Contracts\HasStateAttributesContract;
use A909M\FilamentStateFusion\Contracts\HasStateFusionAction;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Spatie\ModelStates\State;

class StateFusionBulkAction extends BulkAction implements HasStateAttributesContract, HasStateFusionAction
{
    use HasStateAttributes;
    use InteractsWithStateAction;
    use ResolvesActionAttributes;

    public string | State | null $fromState = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->label(fn () => $this->resolveLabel($this->getFromState()));
        $this->color(fn () => $this->resolveColor($this->getFromState()));
        $this->icon(fn () => $this->resolveIcon($this->getFromState()));
        $this->tooltip(fn () => $this->resolveDescription($this->getFromState()));
        $this->setActionAttributes();
        $this->action(function (Collection $records, $data) {
            $records->each(callback: function ($record) use ($data) {
                if ($record->{$this->getAttribute()}->equals($this->getFromState())) {
                    if (empty($data)) {
                        if ($record->{$this->getAttribute()}->canTransitionTo($this->getToStateClass())) {
                            return $record->{$this->getAttribute()}->transitionTo($this->getToStateClass());
                        }
                    } else {
                        if ($record->{$this->getAttribute()}->canTransitionTo($this->getToStateClass(), $data)) {
                            return $record->{$this->getAttribute()}->transitionTo($this->getToStateClass(), $data);
                        }
                    }
                }
            });
        });
    }

    public function transition(string | State | null $fromState, string | State | null $toState): self
    {
        $this->toState = $toState;
        $this->fromState = $fromState;

        return $this;
    }

    public function getFromState(): string | State | null
    {
        return $this->evaluate($this->fromState);
    }
}
