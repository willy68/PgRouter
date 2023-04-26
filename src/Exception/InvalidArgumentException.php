<?php

declare(strict_types=1);

namespace PgRouter\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;

/** @final */
class InvalidArgumentException extends PhpInvalidArgumentException implements ExceptionInterface
{
}
