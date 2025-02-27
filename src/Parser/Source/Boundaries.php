<?php

/**
 * PackageFactory.ComponentEngine - Universal View Components for PHP
 *   Copyright (C) 2022 Contributors of PackageFactory.ComponentEngine
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace PackageFactory\ComponentEngine\Parser\Source;

final class Boundaries implements \JsonSerializable
{
    private function __construct(
        public readonly Position $start,
        public readonly Position $end,
    ) {
    }

    public static function fromPositions(Position $start, Position $end): self
    {
        return new self($start, $end);
    }

    public function expandedTo(Boundaries $other): self
    {
        return new self($this->start, $other->end);
    }

    public function equals(Boundaries $other): bool
    {
        return ($this->start->equals($other->start)
            && $this->end->equals($other->end));
    }

    public function jsonSerialize(): mixed
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}
