<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Narrowspark\Automatic\Common;

use DateTime;
use DateTimeImmutable;
use Exception;
use Narrowspark\Automatic\Common\Contract\Package as PackageContract;

final class Package implements PackageContract
{
    /** @var string[] */
    private const KEY_TO_FUNCTION_MAPPERS = [
        'parent' => 'setParentName',
        'is-dev' => 'setIsDev',
        'url' => 'setUrl',
        'operation' => 'setOperation',
        'type' => 'setType',
        'requires' => 'setRequires',
        'automatic-extra' => 'setConfig',
        'autoload' => 'setAutoload',
        'created' => 'setTime',
    ];

    /**
     * The package name.
     *
     * @var string
     */
    private $name;

    /**
     * The pretty package name.
     *
     * @var string
     */
    private $prettyName;

    /**
     * The name of the parent package.
     *
     * @var null|string
     */
    private $parentName;

    /**
     * The package pretty version.
     *
     * @var null|string
     */
    private $prettyVersion;

    /**
     * The package type.
     *
     * @var null|string
     */
    private $type;

    /**
     * The package url.
     *
     * @var null|string
     */
    private $url;

    /**
     * The package operation.
     *
     * @var null|string
     */
    private $operation;

    /**
     * The package requires.
     *
     * @var array
     */
    private $requires = [];

    /**
     * The automatic package config.
     *
     * @var array
     */
    private $configs = [];

    /**
     * The package autoload values.
     *
     * @var array
     */
    private $autoload = [];

    /**
     * Check if this package is a dev require.
     *
     * @var bool
     */
    private $isDev = false;

    /**
     * Timestamp of the object creation.
     *
     * @var string
     */
    private $created;

    /**
     * Create a new Package instance.
     *
     * @throws Exception
     */
    public function __construct(string $name, ?string $prettyVersion)
    {
        $this->prettyName = $name;
        $this->name = \strtolower($name);
        $this->prettyVersion = $prettyVersion;
        $this->created = (new DateTimeImmutable())->format(DateTime::RFC3339);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name): PackageContract
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrettyName(): string
    {
        return $this->prettyName;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentName(string $name): PackageContract
    {
        $this->parentName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrettyVersion(): ?string
    {
        return $this->prettyVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType(string $type): PackageContract
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl(string $url): PackageContract
    {
        $this->url = $url;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * {@inheritdoc}
     */
    public function setOperation(string $operation): PackageContract
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]
     */
    public function getRequires(): array
    {
        return $this->requires;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequires(array $requires): PackageContract
    {
        $this->requires = $requires;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]
     */
    public function getAutoload(): array
    {
        return $this->autoload;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed[] $autoload
     */
    public function setAutoload(array $autoload): PackageContract
    {
        $this->autoload = $autoload;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDev(): bool
    {
        return $this->isDev;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDev(bool $bool = true): PackageContract
    {
        $this->isDev = $bool;

        return $this;
    }

    /**
     * Create a automatic package from the lock data.
     */
    public static function createFromLock(string $name, array $packageData): PackageContract
    {
        $package = new self($name, $packageData['version']);

        foreach ($packageData as $key => $data) {
            if ($data !== null && isset(self::KEY_TO_FUNCTION_MAPPERS[$key])) {
                $package->{self::KEY_TO_FUNCTION_MAPPERS[$key]}($data);
            }
        }

        return $package;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed[] $configs
     */
    public function setConfig(array $configs): PackageContract
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfig(string $mainKey, ?string $name = null): bool
    {
        $mainCheck = \array_key_exists($mainKey, $this->configs);

        if ($name === null) {
            return $mainCheck;
        }

        if ($mainCheck && \is_array($this->configs[$mainKey])) {
            return \array_key_exists($name, $this->configs[$mainKey]);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|mixed
     */
    public function getConfig(string $mainKey, ?string $name = null)
    {
        if (\array_key_exists($mainKey, $this->configs)) {
            if ($name === null) {
                return $this->configs[$mainKey];
            }

            if (\is_array($this->configs[$mainKey]) && \array_key_exists($name, $this->configs[$mainKey])) {
                return $this->configs[$mainKey][$name];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTime(string $time): PackageContract
    {
        $this->created = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTime(): string
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'pretty-name' => $this->prettyName,
            'version' => $this->prettyVersion,
            'parent' => $this->parentName,
            'is-dev' => $this->isDev,
            'url' => $this->url,
            'operation' => $this->operation,
            'type' => $this->type,
            'requires' => $this->requires,
            'automatic-extra' => $this->configs,
            'autoload' => $this->autoload,
            'created' => $this->created,
        ];
    }
}
