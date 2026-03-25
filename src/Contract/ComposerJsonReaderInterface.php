<?php
declare(strict_types=1);

namespace SuperKernel\ComposerResolver\Contract;

use ArrayAccess;
use IteratorAggregate;

interface ComposerJsonReaderInterface extends ArrayAccess, IteratorAggregate
{
}