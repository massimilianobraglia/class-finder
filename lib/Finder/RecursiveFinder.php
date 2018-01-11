<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Finder;

use Kcs\ClassFinder\Iterator\RecursiveIterator;
use Kcs\ClassFinder\PathNormalizer;

final class RecursiveFinder implements FinderInterface
{
    use ReflectionFilterTrait;

    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $path = PathNormalizer::resolvePath($path);
        $this->path = $path;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return $this->applyFilters(new RecursiveIterator($this->path));
    }
}