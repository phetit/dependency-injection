<?php

declare(strict_types=1);

namespace Phetit\Container;

use Phetit\Container\Exception\EntryNotFoundException;
use Phetit\Container\Exception\InvalidEntryIdentifierException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * The container's parameters
     *
     * @var mixed[]
     */
    protected array $parameters = [];

    /**
     * The container's services
     *
     * @var callable[]
     */
    protected array $services = [];

    /**
     * The container's statics
     *
     * @var callable[]
     */
    protected array $statics = [];

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->parameters) || isset($this->services[$id]) || isset($this->statics[$id]);
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->parameters)) {
            return $this->parameters[$id];
        }

        if (isset($this->services[$id])) {
            return $this->services[$id]($this);
        }

        if (isset($this->statics[$id])) {
            $this->parameters[$id] = $this->statics[$id]($this);

            return $this->parameters[$id];
        }

        throw new EntryNotFoundException();
    }

    /**
     * Register parameters that returns the value set
     */
    public function parameter(string $id, mixed $value): void
    {
        $this->validateIdentifier($id);

        $this->parameters[$id] = $value;
    }

    /**
     * Register a service that is resolved in every call to `get()` method
     */
    public function register(string $id, callable $resolver): void
    {
        $this->validateIdentifier($id);

        $this->services[$id] = $resolver;
    }

    /**
     * Register a service that is resolved only the first time `get()` is called
     */
    public function static(string $id, callable $resolver): void
    {
        $this->validateIdentifier($id);

        $this->statics[$id] = $resolver;
    }

    /**
     * Validate entry identifier
     *
     * @throws InvalidEntryIdentifierException if $id is an empty string.
     */
    protected function validateIdentifier(string $id): void
    {
        if ($id === '') {
            throw new InvalidEntryIdentifierException($id);
        }
    }
}
