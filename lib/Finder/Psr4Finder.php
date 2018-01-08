<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Finder\Iterator\Psr4Iterator;

/**
 * Finds classes respecting psr-4 standard.
 */
final class Psr4Finder implements FinderInterface
{
    use FinderTrait;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $namespace, string $path)
    {
        $this->namespace = $namespace;
        $this->path = $path;
    }

    public function getIterator(): \Iterator
    {
        return $this->applyFilters(new Psr4Iterator($this->namespace, $this->path));
    }
}