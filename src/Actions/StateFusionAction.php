<?php

namespace A909M\FilamentStateFusion\Actions;

use A909M\FilamentStateFusion\Concerns\HasStateAttributes;
use A909M\FilamentStateFusion\Concerns\InteractsWithStateAction;
use A909M\FilamentStateFusion\Contracts\HasStateAttributesContract;
use A909M\FilamentStateFusion\Contracts\HasStateFusionAction;
use Filament\Actions\Action;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Model;

class StateFusionAction extends Action implements HasStateAttributesContract, HasStateFusionAction
{
    use HasStateAttributes;
    use InteractsWithStateAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn(Model $record) => $this->resolveLabel($record));
        $this->color(fn(Model $record) => $this->resolveColor($record));
        $this->icon(fn(Model $record) => $this->resolveIcon($record));

        $this->hidden(fn($record) => !in_array(
            $this->getToState()::getMorphClass(),
            $record->{$this->getAttribute()}->transitionableStates(),
        ));

        $this->action(function ($record, array $data): void {
            if (empty($data)) {
                $record->{$this->getAttribute()}->transitionTo($this->getToStateClass());
            } else {
                $record->{$this->getAttribute()}->transitionTo($this->getToStateClass(), $data[array_key_first($data)]);
            }

            $this->success();
        });
        // $this->badge();
        // $this->button();
        $this->modalDescription(fn(Model $record) => $this->resolveDescription($record));
        $this->modalIcon(fn() => $this->getIcon());
        $this->modalIconColor(fn() => $this->getColor());
        $this->requiresConfirmation();
    }

    private function resolveFromTransitionOrState(Model $record, string $interface, string $method): mixed
    {
        $from = $record->{$this->getAttribute()};
        $to = $this->getToState();
        $toInstance = new $to($record);
        $transitionClass = $from::config()->resolveTransitionClass(
            $from::getMorphClass(),
            $toInstance::getMorphClass(),
        );

        if ($transitionClass && class_exists($transitionClass) && is_subclass_of($transitionClass, $interface)) {
            return app($transitionClass)->{$method}();
        }

        if ($toInstance && is_subclass_of($toInstance, $interface)) {
            return $toInstance->{$method}();
        }

        return null;
    }

    private function resolveLabel(Model $record): null|string
    {
        return $this->resolveFromTransitionOrState($record, HasLabel::class, 'getLabel');
    }

    private function resolveColor(Model $record): null|string
    {
        return $this->resolveFromTransitionOrState($record, HasColor::class, 'getColor');
    }

    private function resolveIcon(Model $record): null|string
    {
        return $this->resolveFromTransitionOrState($record, HasIcon::class, 'getIcon');
    }

    private function resolveDescription(Model $record): null|string
    {
        return $this->resolveFromTransitionOrState($record, HasDescription::class, 'getDescription');
    }
}
