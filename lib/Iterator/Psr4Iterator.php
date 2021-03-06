<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Iterator;

use Kcs\ClassFinder\PathNormalizer;

final class Psr4Iterator extends ClassIterator
{
    use PsrIteratorTrait;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var int
     */
    private $prefixLen;

    /**
     * @var string[]
     */
    private $classMap;

    public function __construct(string $namespace, string $path, int $flags = 0, array $classMap = [])
    {
        $this->namespace = $namespace;
        $this->path = PathNormalizer::resolvePath($path);
        $this->prefixLen = \strlen($this->path);
        $this->classMap = \array_map(PathNormalizer::class.'::resolvePath', $classMap);

        parent::__construct($flags);
    }

    protected function getGenerator(): \Generator
    {
        $pattern = \defined('HHVM_VERSION') ? '/\\.(php|hh)$/i' : '/\\.php$/i';
        $include = \Closure::bind(static function (string $path) {
            include_once $path;
        }, null, null);

        foreach ($this->search() as $path => $info) {
            if (! \preg_match($pattern, $path, $m) || ! $info->isReadable()) {
                continue;
            }

            if (\in_array($path, $this->classMap, true)) {
                continue;
            }

            $class = $this->namespace.\ltrim(\str_replace('/', '\\', \substr($path, $this->prefixLen, -\strlen($m[0]))), '\\');
            if (! \preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+)*+$/', $class)) {
                continue;
            }

            // Due to composer bug #6987 and the refuse of think about a proper
            // solution, we are forced to include the file here and check if class
            // exists with autoload flag disabled (see method exists).

            try {
                $include($path);
            } catch (\Throwable $e) {
                continue;
            }

            if (! $this->exists($class)) {
                continue;
            }

            yield $class => new \ReflectionClass($class);
        }
    }
}
