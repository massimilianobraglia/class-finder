<?php declare(strict_types=1);

namespace Kcs\ClassFinder\Tests\Iterator;

use Composer\Autoload\ClassLoader;
use Kcs\ClassFinder\Fixtures\Psr0;
use Kcs\ClassFinder\Fixtures\Psr4;
use Kcs\ClassFinder\Iterator\ClassIterator;
use Kcs\ClassFinder\Iterator\ComposerIterator;
use PHPUnit\Framework\TestCase;

class ComposerIteratorTest extends TestCase
{
    public function testComposerIteratorShouldSearchInClassMap()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->classMap = [
                ClassIterator::class => __DIR__.'/.'.'/ClassBax.php',
                ComposerIterator::class => __DIR__.'/.'.'/FooBar.php',
            ];
        }, null, ClassLoader::class))();
        $iterator = new ComposerIterator($loader);

        self::assertEquals([
            ClassIterator::class => new \ReflectionClass(ClassIterator::class),
            ComposerIterator::class => new \ReflectionClass(ComposerIterator::class),
        ], \iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldNotSearchesInPsrPrefixedDirsIfClassmapAuthoritativeFlagIsEnabled()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->classMap = [
                ClassIterator::class => __DIR__.'/.'.'/ClassBax.php',
                ComposerIterator::class => __DIR__.'/.'.'/FooBar.php',
            ];
            $loader->classMapAuthoritative = true;

            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\' => [
                    __DIR__.'/../..'.'/lib',
                ],
            ];
        }, null, ClassLoader::class))();
        $iterator = new ComposerIterator($loader);

        self::assertEquals([
            ClassIterator::class => new \ReflectionClass(ClassIterator::class),
            ComposerIterator::class => new \ReflectionClass(ComposerIterator::class),
        ], \iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldSearchInPsr4PrefixedDir()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__.'/../..'.'/data/Composer/Psr4',
                ],
            ];
        }, null, ClassLoader::class))();

        $iterator = new ComposerIterator($loader);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], \iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldSearchInPsr0PrefixedDir()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__.'/../..'.'/data/Composer/Psr4',
                ],
            ];

            $loader->prefixesPsr0 = [
                'K' => [
                    'Kcs\\ClassFinder\\Fixtures\\Psr0\\' => [
                        __DIR__.'/../..'.'/data/Composer/Psr0',
                    ],
                ],
            ];
        }, null, ClassLoader::class))();

        $iterator = new ComposerIterator($loader);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
            Psr0\BarBar::class => new \ReflectionClass(Psr0\BarBar::class),
            Psr0\Foobar::class => new \ReflectionClass(Psr0\Foobar::class),
            Psr0\SubNs\FooBaz::class => new \ReflectionClass(Psr0\SubNs\FooBaz::class),
        ], \iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldSkipNonInstantiableClass()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__.'/../..'.'/data/Composer/Psr4',
                ],
            ];
        }, null, ClassLoader::class))();

        $iterator = new ComposerIterator($loader, ClassIterator::SKIP_NON_INSTANTIABLE);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], \iterator_to_array($iterator));
    }

    public function testComposerIteratorShouldNotYieldTheSameClassTwice()
    {
        $loader = new ClassLoader();
        (\Closure::bind(function () use ($loader) {
            $loader->classMap = [
                Psr4\FooInterface::class => __DIR__.'/../..'.'/data/Composer/Psr4/FooInterface.php',
                Psr4\FooTrait::class => __DIR__.'/../..'.'/data/Composer/Psr4/FooTrait.php',
            ];
            $loader->prefixDirsPsr4 = [
                'Kcs\\ClassFinder\\Fixtures\\Psr4\\' => [
                    __DIR__.'/../..'.'/data/Composer/Psr4',
                ],
            ];
        }, null, ClassLoader::class))();

        $iterator = new ComposerIterator($loader);

        self::assertEquals([
            Psr4\BarBar::class => new \ReflectionClass(Psr4\BarBar::class),
            Psr4\Foobar::class => new \ReflectionClass(Psr4\Foobar::class),
            Psr4\AbstractClass::class => new \ReflectionClass(Psr4\AbstractClass::class),
            Psr4\FooInterface::class => new \ReflectionClass(Psr4\FooInterface::class),
            Psr4\FooTrait::class => new \ReflectionClass(Psr4\FooTrait::class),
            Psr4\SubNs\FooBaz::class => new \ReflectionClass(Psr4\SubNs\FooBaz::class),
        ], \iterator_to_array($iterator));
    }
}
