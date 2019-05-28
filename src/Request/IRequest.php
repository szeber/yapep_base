<?php
declare(strict_types=1);

namespace YapepBase\Request;

use Emul\Server\ServerData;
use YapepBase\Request\Source\CustomParams;
use YapepBase\Request\Source\IFiles;
use YapepBase\Request\Source\ISource;

interface IRequest
{
    /**
     * Returns the Query Param (GET)
     */
    public function getQueryParams(): ISource;

    /**
     * Returns the Post params
     */
    public function getPostParams(): ISource;

    /**
     * Returns the cookies
     */
    public function getCookies(): ISource;

    /**
     * Returns the environment params
     */
    public function getEnvParams(): ISource;

    /**
     * Returns the input params, retrieved from (php://input)
     */
    public function getInputParams(): ISource;

    /**
     * Returns the uploaded files
     */
    public function getFiles(): IFiles;

    /**
     * Returns the Server data
     */
    public function getServer(): ServerData;

    /**
     * Returns the custom set params (for example routing params)
     */
    public function getCustomParams(): CustomParams;

    /**
     * Returns the requested param from Customer, Query or Pos params in the mentioned order
     */
    public function getParamAsInt(string $name, ?int $default = null): ?int;

    /**
     * Returns the requested param from Customer, Query or Pos params in the mentioned order
     */
    public function getParamAsString(string $name, ?string $default = null): ?string;

    /**
     * Returns the requested param from Customer, Query or Pos params in the mentioned order
     */
    public function getParamAsFloat(string $name, ?float $default = null): ?float;

    /**
     * Returns the requested param from Customer, Query or Pos params in the mentioned order
     */
    public function getParamAsArray(string $name, ?array $default = []): ?array;

    /**
     * Returns the requested param from Customer, Query or Pos params in the mentioned order
     */
    public function getParamAsBool(string $name, ?bool $default = null): ?bool;

    /**
     * Returns the target of the request
     */
    public function getTargetUri(): string;

    /**
     * Returns the request method
     */
    public function getMethod(): string;

    /**
     * Returns the protocol
     */
    public function getProtocol(): string;
}
