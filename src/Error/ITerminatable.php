<?php
declare(strict_types=1);

namespace YapepBase\ErrorHandler;

/**
 * Interface for terminatable objects.
 *
 * Classes implementing this interface may be registered as terminators to the ErrorHandlerRegistry, and the
 * terminate() method will be called last before the application ends.
 *
 * The terminate() method is usable to cleanly exit and also return an exit code.
 */
interface ITerminatable
{
    public function terminate(bool $isFatalError): void;
}
