<?php
declare(strict_types=1);

namespace YapepBase\Exception;

/**
 * RedirectException class.
 *
 * Not descendant of YapepBase\Exception\Exception, and it should only be catched by the Application
 * or a controller if necessary.
 */
class RedirectException extends \Exception
{
    const TYPE_INTERNAL = 1;
    const TYPE_EXTERNAL = 2;

    /** @var string */
    protected $target;

    public function __construct(string $target, int $type, ?\Exception $previous = null)
    {
        $message      = 'Redirecting to: ' . $target;
        $this->target = $target;

        parent::__construct($message, $type, $previous);
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
