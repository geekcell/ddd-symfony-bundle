<?php

declare(strict_types=1);

namespace GeekCell\DDDBundle\Domain\Model;

use GeekCell\DDDBundle\Support\Traits\DispatchableTrait;

/**
 * Abstract class AggregateRoot.
 *
 * @package GeekCell\DDDBundle\Domain\Model
 * @codeCoverageIgnore
 */
abstract class AggregateRoot
{
    use DispatchableTrait;
}
