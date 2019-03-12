<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Session;

use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Event\Event;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\Exception;
use YapepBase\Storage\IStorage;
use YapepBase\Util\Random;

/**
 * Base class for session handlers.
 *
 * Configuration settings for the sessions should be set in the format:
 * <b>resource.session.&lt;configName&gt;.&lt;optionName&gt;
 *
 * Configuration options:
 * <ul>
 *     <li>namespace: The namespace used for the session. This namespace is used to register to the session
 *                    registry, and also used as part of the key used during the storage of the session data.</li>
 *     <li>lifetime:  The lifetime of the sesssion in seconds. Optional.</li>
 * </ul>
 */
abstract class SessionAbstract implements ISession
{
    /** The default session lifetime, if one is not provided in the config. */
    const DEFAULT_LIFETIME = 1800;

    /**
     * The storage instance.
     *
     * @var \YapepBase\Storage\IStorage
     */
    protected $storage;

    /**
     * The session lifetime in seconds.
     *
     * @var int
     */
    protected $lifetime = self::DEFAULT_LIFETIME;

    /**
     * The namespace used by the session.
     *
     * @var string
     */
    protected $namespace;

    /**
     * TRUE if the session data has already been loaded.
     *
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * The session ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The session data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param string                        $configName     Name of the session config.
     * @param \YapepBase\Storage\IStorage   $storage        The storage object.
     * @param bool                          $autoRegister   If TRUE, it will automatically register as an event handler.
     *
     * @throws \YapepBase\Exception\ConfigException   On configuration problems
     * @throws \YapepBase\Exception\Exception         On other problems
     */
    public function __construct($configName, IStorage $storage, $autoRegister = true)
    {
        if (!$storage->isTtlSupported()) {
            throw new Exception('Storage without TTL support passed to session handler.');
        }

        $this->storage  = $storage;

        $properties = [
            'namespace',
            'lifetime',
        ];
        $properties = array_merge($properties, $this->getConfigProperties());
        $configData = [];
        foreach ($properties as $property) {
            try {
                $configData[$property] =
                    Config::getInstance()->get('resource.session.' . $configName . '.' . $property);
            } catch (ConfigException $e) {
                // We just swallow this because we don't know what properties do we need in advance
            }
        }

        if (empty($configData)) {
            throw new ConfigException('Configuration not found for session handler: ' . $configName);
        }
        if (!isset($configData['namespace'])) {
            throw new ConfigException('No namespace has been set for the session handler: ' . $configName);
        }
        $this->validateConfig($configData);

        $this->namespace = $configData['namespace'];
        $this->lifetime  = empty($configData['lifetime'])
            ? self::DEFAULT_LIFETIME
            : (int)$configData['lifetime'];

        if ($autoRegister) {
            $this->registerEventHandler();
        }

        $this->id = $this->getSessionId();
    }

    /**
     * Returns the config properties(last part of the key) used by the class.
     *
     * @return array
     */
    abstract protected function getConfigProperties();

    /**
     * Validates the configuration.
     *
     * @param array $config   The configuration array.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ConfigException   On configuration problems
     * @throws \YapepBase\Exception\Exception         On other problems
     */
    abstract protected function validateConfig(array $config);

    /**
     * Returns the session ID. If there is no session ID, it returns NULL.
     *
     * @return string
     */
    abstract protected function getSessionId();

    /**
     * This method is called when the session has been initialized (loaded or created).
     *
     * @return void
     */
    protected function sessionInitialized()
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * This method is called if an invalid session ID is received.
     *
     * It can be used for example to log the request. The method should not return anything, and not stop execution.
     *
     * @return void
     */
    protected function nonExistentSessionId()
    {
        // Empty default implementation. Should be implemented by descendant classes if needed
    }

    /**
     * Registers the instance as an event handler
     *
     * @return void
     */
    public function registerEventHandler()
    {
        $registry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
        $registry->registerEventHandler(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN, $this);
        $registry->registerEventHandler(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN, $this);
    }

    /**
     * Removes event handler registration
     *
     * @return void
     */
    public function removeEventHandler()
    {
        $registry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
        $registry->removeEventHandler(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN, $this);
        $registry->removeEventHandler(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN, $this);
    }

    /**
     * Returns the session ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the key used to store the session data
     *
     * @return string
     */
    protected function getStorageKey()
    {
        return 'session.' . $this->namespace . '.' . $this->id;
    }

    /**
     * Loads the session.
     *
     * If there is no session ID set, it creates a new session instead.
     *
     * @return void
     */
    public function loadSession()
    {
        if ($this->isLoaded) {
            return;
        }
        if (empty($this->id)) {
            $this->create();

            return;
        }
        $result = $this->storage->get($this->getStorageKey());
        if (false === $result) {
            $this->nonExistentSessionId();
            $this->create();

            return;
        }
        $this->data     = $result;
        $this->isLoaded = true;
        $this->sessionInitialized();
    }

    /**
     * Saves the session.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\Exception   If trying to save a not loaded session
     */
    public function saveSession()
    {
        if (!$this->isLoaded) {
            throw new Exception('Saving a session that has not been loaded yet');
        }
        $this->storage->set($this->getStorageKey(), $this->data, $this->lifetime);
    }

    /**
     * Creates a new session.
     *
     * @return void
     */
    public function create()
    {
        if (!empty($this->id)) {
            $this->destroy();
        }
        $this->id       = $this->generateId();
        $this->data     = [];
        $this->isLoaded = true;
        $this->sessionInitialized();
    }

    /**
     * Generates a session ID.
     *
     * @return string
     */
    protected function generateId()
    {
        return Random::getPseudoString(32);
    }

    /**
     * Destroys the session.
     *
     * @return void
     */
    public function destroy()
    {
        $this->storage->delete($this->getStorageKey());
        $this->data     = [];
        $this->isLoaded = false;
        $this->id       = null;
    }

    /**
     * Handles the application start and finish events to load and save the session.
     *
     * @param \YapepBase\Event\Event $event   The event.
     *
     * @return void
     *
     * @see YapepBase\Event.IEventHandler::handleEvent()
     */
    public function handleEvent(Event $event)
    {
        switch ($event->getType()) {
            case Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN:
                $this->loadSession();
                break;

            case Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN:
                $this->saveSession();
                break;
        }
    }

    /**
     * Returns the session's namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Checks whether a key is set in the session
     *
     * @param string $offset   The offset.
     *
     * @return bool
     *
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Returns a key from the session
     *
     * @param string $offset   The offset to return.
     *
     * @return mixed
     *
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }

        return null;
    }

    /**
     * Sets a key in the session
     *
     * @param string $offset   The offset.
     * @param mixed  $value    The value.
     *
     * @return void
     *
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Removes a key from the session
     *
     * @param string $offset   The offset.
     *
     * @return void
     *
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if (isset($this->data[$offset])) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns only the data from the session.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
