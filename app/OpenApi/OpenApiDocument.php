<?php

namespace App\OpenApi;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final class OpenApiDocument
{
    /** @var array<string, true> */
    private array $resolving = [];

    public function __construct(
        private readonly string $rootFile,
        private readonly string $allowedRoot,
    ) {}

    public static function fromBasePath(): self
    {
        return new self(base_path('openapi.yaml'), base_path());
    }

    /**
     * @return array<string, mixed>
     */
    public function bundled(): array
    {
        $document = $this->load($this->rootFile);

        if (! $this->isMap($document)) {
            throw new RuntimeException('OpenAPI document must be a YAML mapping.');
        }

        if (isset($document['openapi'])) {
            $document['openapi'] = (string) $document['openapi'];
        }

        return $document;
    }

    public function bundledJson(): string
    {
        return json_encode(
            $this->bundled(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    private function load(string $filePath): mixed
    {
        $realPath = realpath($filePath);

        if ($realPath === false || ! is_file($realPath)) {
            throw new RuntimeException("OpenAPI file not found: {$filePath}");
        }

        $this->assertWithinProject($realPath);

        if (isset($this->resolving[$realPath])) {
            throw new RuntimeException("Circular OpenAPI \$ref: {$realPath}");
        }

        $this->resolving[$realPath] = true;

        try {
            return $this->resolveRefs(Yaml::parseFile($realPath), dirname($realPath));
        } finally {
            unset($this->resolving[$realPath]);
        }
    }

    private function resolveRefs(mixed $node, string $baseDir): mixed
    {
        if (! is_array($node)) {
            return $node;
        }

        if ($this->isExternalRef($node)) {
            return $this->resolveExternalRef($node, $baseDir);
        }

        foreach ($node as $key => $value) {
            $node[$key] = $this->resolveRefs($value, $baseDir);
        }

        return $node;
    }

    /**
     * @param  array<array-key, mixed>  $node
     */
    private function isExternalRef(array $node): bool
    {
        return isset($node['$ref'])
            && is_string($node['$ref'])
            && ! str_starts_with($node['$ref'], '#');
    }

    /**
     * @param  array<array-key, mixed>  $node
     */
    private function resolveExternalRef(array $node, string $baseDir): mixed
    {
        $ref = $node['$ref'];

        if (! is_string($ref) || $ref === '') {
            throw new RuntimeException('OpenAPI $ref must be a non-empty string.');
        }

        unset($node['$ref']);

        $path = $ref;
        $fragment = '';

        if (str_contains($ref, '#')) {
            [$path, $fragment] = explode('#', $ref, 2);
        }

        $resolved = $this->load($this->resolvePath($baseDir, $path));

        if ($fragment !== '') {
            $resolved = $this->getJsonPointer($resolved, $fragment);
        }

        if ($node === []) {
            return $resolved;
        }

        if (! $this->isMap($resolved) || ! $this->isMap($node)) {
            return $resolved;
        }

        return $this->resolveRefs(array_merge($resolved, $node), $baseDir);
    }

    private function resolvePath(string $baseDir, string $ref): string
    {
        $baseRealPath = realpath($baseDir);

        if ($baseRealPath === false || $ref === '') {
            throw new RuntimeException("Invalid OpenAPI \$ref: {$ref}");
        }

        return $baseRealPath.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ref);
    }

    private function getJsonPointer(mixed $document, string $pointer): mixed
    {
        $segments = array_values(array_filter(explode('/', $pointer), fn (string $segment): bool => $segment !== ''));
        $current = $document;

        foreach ($segments as $segment) {
            $key = str_replace(['~1', '~0'], ['/', '~'], $segment);

            if (! is_array($current) || ! array_key_exists($key, $current)) {
                throw new RuntimeException("OpenAPI \$ref pointer not found: #/{$pointer}");
            }

            $current = $current[$key];
        }

        return $current;
    }

    private function assertWithinProject(string $realPath): void
    {
        $root = realpath($this->allowedRoot);

        if ($root === false) {
            throw new RuntimeException('Unable to resolve the OpenAPI root path.');
        }

        if ($realPath !== $root && ! str_starts_with($realPath, $root.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException("OpenAPI \$ref escapes the project root: {$realPath}");
        }
    }

    /**
     * @phpstan-assert-if-true array<string, mixed> $value
     */
    private function isMap(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (! is_string($key)) {
                return false;
            }
        }

        return true;
    }
}
