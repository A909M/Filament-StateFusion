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

        $this->label(fn (Model $record) => $this->resolveLabel($record->{$this->getAttribute()}));
        $this->color(fn (Model $record) => $this->resolveColor($record->{$this->getAttribute()}));
        $this->icon(fn (Model $record) => $this->resolveIcon($record->{$this->getAttribute()}));
        $this->tooltip(fn (Model $record) => $this->resolveDescription($record->{$this->getAttribute()}));
        $this->setActionAttributes();

        $this->hidden(function (Model $record) {
            return ! $record?->{$this->getAttribute()}?->canTransitionTo($this->getToStateClass());
        });

        $this->action(function ($record, array $data): void {
            if (empty($data)) {
                $record->{$this->getAttribute()}->transitionTo($this->getToStateClass());
            } else {
                $record->{$this->getAttribute()}->transitionTo($this->getToStateClass(), $data);
            }

            $this->success();
        });
        $this->after(fn ($record) => $record->refresh());

        // $this->badge();
        // $this->button();
    }
}
