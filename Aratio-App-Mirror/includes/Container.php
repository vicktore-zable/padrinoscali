<?php

class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];

    public function set(string $id, $value): void
    {
        $this->bindings[$id] = $value;
    }

    public function singleton(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
        $this->instances[$id] = null;
    }

    public function alias(string $alias, string $target): void
    {
        $this->aliases[$alias] = $target;
    }

    public function has(string $id): bool
    {
        $id = $this->resolveAlias($id);
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function get(string $id)
    {
        $id = $this->resolveAlias($id);

        // Return existing instance
        if (isset($this->instances[$id])) {
            if ($this->instances[$id] !== null) return $this->instances[$id];
            $this->instances[$id] = $this->resolve($id);
            return $this->instances[$id];
        }

        // Singleton with null instance (not yet resolved)
        if (isset($this->bindings[$id]) && is_callable($this->bindings[$id])) {
            return $this->resolve($id);
        }

        // Direct value
        if (isset($this->bindings[$id])) {
            return $this->bindings[$id];
        }

        // Auto-resolve class
        if (class_exists($id)) {
            return $this->autoResolve($id);
        }

        throw new RuntimeException("Service not found: $id");
    }

    private function resolve(string $id)
    {
        $factory = $this->bindings[$id] ?? null;
        if (is_callable($factory)) {
            return $factory($this);
        }
        return $factory;
    }

    private function autoResolve(string $class)
    {
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();

        if (!$constructor) return $ref->newInstance();

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                $params[] = $this->get($typeName);
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                $params[] = null;
            }
        }

        return $ref->newInstanceArgs($params);
    }

    private function resolveAlias(string $id): string
    {
        $seen = [];
        while (isset($this->aliases[$id])) {
            if (isset($seen[$id])) throw new RuntimeException("Circular alias: $id");
            $seen[$id] = true;
            $id = $this->aliases[$id];
        }
        return $id;
    }
}
