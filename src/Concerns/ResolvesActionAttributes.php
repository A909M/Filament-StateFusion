<?php

namespace A909M\FilamentStateFusion\Concerns;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\State;

trait ResolvesActionAttributes
{
    private function resolveFromTransitionOrState(
        State | string | null $state,
        string $interface,
        string $method,
    ): mixed {
        $from = $state ?? $this->getFromState();
        $to = $this->getToStateClass();
        $transitionClass = $from::config()
            ->resolveTransitionClass(
                $from::getMorphClass(),
                $to::getMorphClass(),
            );

        if (
            $transitionClass &&
            class_exists($transitionClass) &&
            is_subclass_of($transitionClass, $interface)
        ) {
            return app($transitionClass)->{$method}();
        }

        if ($to && is_subclass_of($to, $interface)) {
            return $to->{$method}();
        }

        return null;
    }

    private function resolveLabel(State | string | null $state): string | Htmlable | null
    {
        return $this->resolveFromTransitionOrState(
            $state,
            HasLabel::class,
            'getLabel',
        );
    }

    private function resolveColor(State | string | null $state): string | array | null
    {
        return $this->resolveFromTransitionOrState(
            $state,
            HasColor::class,
            'getColor',
        );
    }

    private function resolveIcon(State | string | null $state): string | \BackedEnum | Htmlable | null
    {
        return $this->resolveFromTransitionOrState(
            $state,
            HasIcon::class,
            'getIcon',
        );
    }

    private function resolveDescription(State | string | null $state): ?string
    {
        return $this->resolveFromTransitionOrState(
            $state,
            HasDescription::class,
            'getDescription',
        );
    }

    protected function setActionAttributes(): void
    {

        // Model
        $this->requiresConfirmation();
        $this->modalDescription(fn () => $this->getTooltip());
        $this->modalIcon(fn () => $this->getIcon());
        $this->modalIconColor(fn () => $this->getColor());

        // Form
        $this->schema(function () {
            if ($this->hasTransitionClass() && method_exists($this->getTransitionClass(), 'form')) {
                return app($this->getTransitionClass())->form();
            }

            return null;
        });
    }
}
