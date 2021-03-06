<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Finder;

use Kcs\ClassFinder\Finder\RecursiveFinder;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use PHPUnit\Framework\TestCase;

class RecursiveFinderTest extends TestCase
{
    public function testFinderShouldBeIterable()
    {
        $finder = new RecursiveFinder(__DIR__);
        self::assertInstanceOf(\Traversable::class, $finder);
    }

    public function testFinderShouldFilterByNamespace()
    {
        $finder = new RecursiveFinder(__DIR__.'/../../data/Composer');
        $finder->inNamespace(['Kcs\ClassFinder\Fixtures\Psr4']);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\HiddenClass::class => new \ReflectionClass(Psr4\HiddenClass::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByDirectory()
    {
        $finder = new RecursiveFinder(__DIR__.'/../../data/Composer');
        $finder->in([__DIR__.'/../../data/Composer/Psr0']);

        self::assertEquals([
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByInterfaceImplementation()
    {
        $finder = new RecursiveFinder(__DIR__.'/../../data/Composer');
        $finder->implementationOf(Psr4\FooInterface::class);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterBySuperClass()
    {
        $finder = new RecursiveFinder(__DIR__.'/../../data/Composer');
        $finder->subclassOf(Psr4\AbstractClass::class);

        self::assertEquals([
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByAnnotation()
    {
        $finder = new RecursiveFinder(__DIR__.'/../../data/Composer');
        $finder->annotatedBy(Psr4\SubNs\FooBaz::class);

        self::assertEquals([
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($finder));
    }

    public function testFinderShouldFilterByCallback()
    {
        $finder = new RecursiveFinder(__DIR__.'/../../data/Composer');
        $finder->filter(function (\ReflectionClass $class) {
            return Psr4\AbstractClass::class === $class->getName();
        });

        self::assertEquals([
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
        ], \iterator_to_array($finder));
    }
}
