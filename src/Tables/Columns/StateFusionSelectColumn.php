<?php

namespace A909M\FilamentStateFusion\Tables\Columns;

use Closure;
use Filament\Tables\Columns\SelectColumn;

class StateFusionSelectColumn extends SelectColumn
{
    protected Closure | string | null $attribute = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->selectablePlaceholder(false);
        $this->options(fn ($record) => $record->getCasts()[$this->getAttribute()]::getStatesLabel($record));
        $this->disableOptionWhen(
            function (string $value, $record, $state) {
                return ($state == $value) ? false : ! in_array($value, $record->{$this->getAttribute()}->transitionableStates());
            }
        );
    }

    public function getAttribute(): string
    {
        // * @phpstan-ignore-next-line */
        return $this->evaluate($this->attribute ?? (string) array_key_first($this->getRecord()::class::getDefaultStates()->toArray()));
    }

    public function attribute(string | Closure | null $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }
}
