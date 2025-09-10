<?php

namespace A909M\FilamentStateFusion\Concerns;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Model;

trait ResolvesActionAttributes
{
    private function resolveFromTransitionOrState(
        Model $record,
        string $interface,
        string $method,
    ): mixed {
        $from = $record->{$this->getAttribute()};
        $to = $this->getToState();
        $toInstance = new $to($record);
        $transitionClass = $from::config()
            ->resolveTransitionClass(
                $from::getMorphClass(),
                $toInstance::getMorphClass(),
            );

        if (
            $transitionClass &&
            class_exists($transitionClass) &&
            is_subclass_of($transitionClass, $interface)
        ) {
            return app($transitionClass)->{$method}();
        }

        if ($toInstance && is_subclass_of($toInstance, $interface)) {
            return $toInstance->{$method}();
        }

        return null;
    }

    private function resolveLabel(Model $record): ?string
    {
        return $this->resolveFromTransitionOrState(
            $record,
            HasLabel::class,
            'getLabel',
        );
    }

    private function resolveColor(Model $record): ?string
    {
        return $this->resolveFromTransitionOrState(
            $record,
            HasColor::class,
            'getColor',
        );
    }

    private function resolveIcon(Model $record): ?string
    {
        return $this->resolveFromTransitionOrState(
            $record,
            HasIcon::class,
            'getIcon',
        );
    }

    private function resolveDescription(Model $record): ?string
    {
        return $this->resolveFromTransitionOrState(
            $record,
            HasDescription::class,
            'getDescription',
        );
    }

    protected function setActionAttributes(): void
    {
        $this->label(fn (Model $record) => $this->resolveLabel($record));
        $this->color(fn (Model $record) => $this->resolveColor($record));
        $this->icon(fn (Model $record) => $this->resolveIcon($record));
    }
}
