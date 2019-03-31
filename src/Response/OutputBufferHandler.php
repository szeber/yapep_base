<?php
declare(strict_types=1);

namespace YapepBase\Response;

class OutputBufferHandler
{
    /** @var int */
    protected $originalObLevel;

    public function start()
    {
        if ($this->isStarted()) {
            return;
        }

        $this->originalObLevel = ob_get_level();
        ob_start();
    }

    /**
     * Be aware that this method stops all later opened output buffers too,
     * because in PHP there is no way of referring to a particular level.
     */
    public function stop(): string
    {
        if (!$this->isStarted()) {
            return '';
        }

        $content = '';
        while (ob_get_level() > $this->originalObLevel) {
            $content .= ob_get_clean();
        }

        return $content;
    }

    public function isStarted(): bool
    {
        return !is_null($this->originalObLevel);
    }

    public function clear(): void
    {
        while (ob_get_level() > $this->originalObLevel) {
            ob_end_clean();
        }

        $this->originalObLevel = null;
    }
}
