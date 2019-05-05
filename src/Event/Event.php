<?php
declare(strict_types=1);

namespace YapepBase\Event;

class Event
{
    /** Event type that is raised when the application starts to run. */
    const APPLICATION_STARTED = 'application.started';
    /** Event type that is raised when the application finishes to run. (Should be the last event raised) */
    const APPLICATION_FINISHED = 'application.finished';
    /** Event type that is raised before the controller's run method is called. */
    const APPLICATION_CONTROLLER_BEFORE_RUN = 'application.controllerBeforeRun';
    /** Event type that is raised after the controller's run method finishes. */
    const APPLICATION_CONTROLLER_FINISHED = 'application.controllerFinished';
    /** Event that's sent after the controller finishes and before the output is sent. */
    const APPLICATION_OUTPUT_BEFORE_SEND = 'application.outputBeforeSend';
    /** Event that's sent after the controller finishes and the output is sent. */
    const APPLICATION_OUTPUT_SENT = 'application.outputSent';

    /** @var string */
    private $name;

    /** @var array */
    private $data = [];

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
