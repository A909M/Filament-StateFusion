<?php

namespace A909M\FilamentStateFusion\Actions;

use A909M\FilamentStateFusion\Concerns\HasStateAttributes;
use A909M\FilamentStateFusion\Concerns\InteractsWithStateAction;
use A909M\FilamentStateFusion\Concerns\ResolvesActionAttributes;
use A909M\FilamentStateFusion\Contracts\HasStateAttributesContract;
use A909M\FilamentStateFusion\Contracts\HasStateFusionAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class StateFusionAction extends Action implements HasStateAttributesContract, HasStateFusionAction
{
    use HasStateAttributes;
    use InteractsWithStateAction;
    use ResolvesActionAttributes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setActionAttributes();

        $this->hidden(
            fn ($record) => ! in_array(
                $this->getToState()::getMorphClass(),
                $record->{$this->getAttribute()}->transitionableStates(),
            ),
        );

        $this->action(function ($record, array $data): void {
            if (empty($data)) {
                $record->{$this->getAttribute()}->transitionTo($this->getToStateClass());
            } else {
                $record->{$this->getAttribute()}->transitionTo($this->getToStateClass(), $data);
            }

            $this->success();
        });
        // $this->badge();
        // $this->button();
        $this->schema(function () {
            if ($this->hasTransitionClass() && method_exists($this->getTransitionClass(), 'form')) {
                return app($this->getTransitionClass())->form();
            }

            return null;
        });
        $this->modalDescription(
            fn (Model $record) => $this->resolveDescription($record),
        );
        $this->modalIcon(fn () => $this->getIcon());
        $this->modalIconColor(fn () => $this->getColor());
        $this->requiresConfirmation();
    }
}
