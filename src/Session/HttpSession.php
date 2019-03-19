<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Session;

use YapepBase\Exception\ConfigException;
use YapepBase\Exception\Exception;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\HttpResponse;
use YapepBase\Storage\IStorage;

/**
 * Implements session handling for HTTP requests.
 *
 * For this session handler, the request and response objects must be instances of HttpRequest and HttpResponse.
 *
 * Configuration options:
 * <ul>
 *     <li>namespace: The namespace used for the session. This namespace is used to register to the session
 *                    registry, and also used as part of the key used during the storage of the session data.</li>
 *     <li>lifetime: The lifetime of the session in seconds. Optional.</li>
 *     <li><b>cookieName: Name of the cookie that stores the session ID.</li>
 *     <li>cookieDomain: The domain to use for the session cookie. Optional, if not set it will use the current
 *                       domain.</li>
 *     <li>cookiePath: The path to set for the cookie. Optional, if not set, the path will be set to "/".</li>
 *     <li>cacheLimitersEnabled: If FALSE, no cache limiters will be sent. Optional, defaults to TRUE.
 *                               {@see http://php.net/manual/en/function.session-cache-limiter.php}</li>
 * </ul>
 */
class HttpSession extends SessionAbstract
{
    /**
     * The request instance.
     *
     * @var \YapepBase\Request\HttpRequest
     */
    protected $request;

    /**
     * The response instance.
     *
     * @var \YapepBase\Response\HttpResponse
     */
    protected $response;

    /**
     * Stores the name of the cookie.
     *
     * @var string
     */
    protected $cookieName;

    /**
     * Stores the domain of the cookie.
     *
     * @var string
     */
    protected $cookieDomain;

    /**
     * Stores the path of the cookie
     *
     * @var string
     */
    protected $cookiePath;

    /**
     * If TRUE, the cache limiters will be sent to the client in the response.
     *
     * @var bool
     */
    protected $cacheLimitersEnabled;

    /**
     * Constructor
     *
     * @param string                           $configName     Name of the session config.
     * @param \YapepBase\Storage\IStorage      $storage        The storage object.
     * @param \YapepBase\Request\HttpRequest   $request        The request object.
     * @param \YapepBase\Response\HttpResponse $response       The response object.
     * @param bool                             $autoRegister   If TRUE, it will automatically register as an event
     *                                                         handler.
     *
     * @throws \YapepBase\Exception\ConfigException   On configuration problems
     * @throws \YapepBase\Exception\Exception         On other problems
     */
    public function __construct(
        $configName,
        IStorage $storage,
        HttpRequest $request,
        HttpResponse $response,
        $autoRegister = true
    ) {
        $this->request  = $request;
        $this->response = $response;

        parent::__construct($configName, $storage, $autoRegister);
    }

    /**
     * Returns the config properties(last part of the key) used by the class.
     *
     * @return array
     */
    protected function getConfigProperties()
    {
        return [
            'cacheLimitersEnabled',
            'cookieDomain',
            'cookieName',
            'cookiePath',
        ];
    }

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
    protected function validateConfig(array $config)
    {
        if (!($this->request instanceof HttpRequest)) {
            throw new Exception('The request object is not an HttpRequest instance');
        }

        if (!($this->response instanceof HttpResponse)) {
            throw new Exception('The response object is not an HttpResponse instance');
        }

        if (empty($config['cookieName'])) {
            throw new ConfigException('No cookie name set for the session handler');
        }

        $this->cookieName           = $config['cookieName'];
        $this->cookieDomain         = (empty($config['cookieDomain']) ? null : $config['cookieDomain']);
        $this->cookiePath           = (empty($config['cookiePath']) ? '/' : $config['cookiePath']);
        $this->cacheLimitersEnabled = (empty($config['cacheLimitersEnabled']) ? true : $config['cacheLimitersEnabled']);
    }

    /**
     * Returns the session ID from the request object. If the request has no session, it returns NULL.
     *
     * @return string
     */
    protected function getSessionId()
    {
        return $this->request->getCookie($this->cookieName, null);
    }

    /**
     * This method is called when the session has been initialized (loaded or created).
     *
     * @return void
     *
     * @see YapepBase\Session.SessionAbstract::sessionInitialized()
     *
     * @todo move cache limiter to response&controller
     */
    protected function sessionInitialized()
    {
        parent::sessionInitialized();
        if ($this->cacheLimitersEnabled) {
            $this->response->addHeader('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
            $this->response->addHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
            );
            $this->response->addHeader('Pragma', 'no-cache');
        }
    }

    /**
     * Creates a new session.
     *
     * @return void
     *
     * @see YapepBase\Session.SessionAbstract::create()
     */
    public function create()
    {
        parent::create();

        $this->response->setCookie($this->cookieName, $this->id, 0, $this->cookiePath, $this->cookieDomain);
    }

    /**
     * Destroys the session.
     *
     * @return void
     *
     * @see YapepBase\Session.SessionAbstract::destroy()
     */
    public function destroy()
    {
        parent::destroy();

        $this->response->setCookie($this->cookieName, '', 1, $this->cookiePath, $this->cookieDomain);
    }

    /**
     * Returns the details of the Session Cookie.
     *
     * @param string $name     The name of the cookie will be populated here. (Outgoing parameter)
     * @param string $domain   The domain of the cookie will be populated here. (Outgoing parameter)
     * @param string $path     The path of the cookie will be populated here. (Outgoing parameter)
     *
     * @return void
     */
    public function getCookieDetails(&$name, &$domain, &$path)
    {
        $name   = $this->cookieName;
        $domain = $this->cookieDomain;
        $path   = $this->cookiePath;
    }
}
