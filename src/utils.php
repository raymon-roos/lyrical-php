<?php

declare(strict_types=1);

function dump(...$args): mixed
{
    array_walk($args, fn ($arg) => is_array($arg)
        ? print_r($arg)
        : var_dump($arg));

    return count($args) === 1 ? $args[0] : $args;
}

/**
 * @return never
 */
function dd(...$args): void
{
    dump(...$args);
    exit();
}
