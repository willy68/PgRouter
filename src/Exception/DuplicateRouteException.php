<?php

declare(strict_types=1);

namespace PgRouter\Exception;

use DomainException;

/** @final */
class DuplicateRouteException extends DomainException implements
    ExceptionInterface
{
}
