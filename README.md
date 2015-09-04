# Hack Scanner

Scan your project for Hack code and generate a mapping of valid fully qualified Hack
names to the files in which the names are defined.

This project is just a thin wrapper around the awesome
[definition-finder](https://github.com/fredemmott/definitions-finder) project by
[Fred Emmott](https://github.com/fredemmott).

## Purpose

This library supplies a simple API for defining which files to scan and how to filter the results.
All of the heavy lifting is done by [definition-finder](https://github.com/fredemmott/definitions-finder).

One primary use case for Hack Scanner is to produce an array suitable to pass to HHVM's built in auto-loader.

Another use case, as seen in [HackUnit](https://github.com/hackpack/hackunit) is to find particular classes
or other definitions in a subtree of your project.  In HackUnit's case, the unit tests must be found because
they are marked with user defined attributes.

## Installation

Use [Composer](https://getcomposer.org/download/) to set this project as a dependency of yours.

```sh
$ composer require --prefer-dist hackpack/hack-scanner
```

The dist version is recommended to prevent type checking errors due to tests using some dev
dependencies that shouldn't be needed for your project.

## Usage

The easiest way to use Hack Scanner is to instantiate the builder and call the appropriate methods for your particular needs.
The builder is configured by calling its public methods.  To obtain an instance of the Scanner class, simply call `getScanner()`
on your instance of the builder.

Below are a couple examples of common use cases.  See below for a reference of all available configuration methods.

```php
<?hh

use HackPack\Scanner\Builder;

// Scan all files recursively searching in the script's parent folder for all names.
$scanner = (new Builder())
    ->addPath(__DIR__)
    ->includeAll()
    ->getScanner();

// Recursively scan all files in the two paths as well as the file referenced for class definitions only
$scanner = (new Builder())
    ->addPaths(Vector{'/interesting/path/one', '/interesting/path/two', '../relative/path/to/file.php'})
    ->includeClasses()
    ->getScanner();
```

### Filters

Sometimes scanning an entire directory tree is not desirable, but referencing each child folder/file would be
quite tedious.  Also you may only want to locate a subset of Classes (for example) defined in a particular set of files.
To accomplish this, you may set filters on the builder.

```php
<?hh

use HackPack\Scanner\Builder;

// Scan all files except for those in the test or tests subfolder
$scanner = (new Builder())
    ->addPath(__DIR__)
    ->filterFilenames($path ==> ! preg_match('|' . __DIR__ . '/tests?/|', $path))
    ->includeAll()
    ->getScanner();

// Scan all files for classes that have a name matching the regex
$scanner = (new Builder())
    ->addPath(__DIR__)
    ->includeClasses($c ==> preg_match('/^HackPack\\/', $c->getName()))
```

See [Filters.php](https://github.com/HackPack/hack-scanner/blob/master/src/Filters.php) for the signature of the filters.  Also see [definitions-finder](https://github.com/fredemmott/definitions-finder) for more information about the objects passed to most filters.

### Filters included (Planned)

Many useful filters may be generated using the static methods of `HackPack\Scanner\Filter`.

#### Filter::inNamespace(string $namespace) : GenericFilter
This filter may be applied to any definition type.  It will only allow definitions who are in the namespace given, or a subnamespace.

#### Filter::hasAttribute(string $attributeName, mixed $value = null) : GenericFilter
This filter may be applied to any definition type.  It will allow definitions that have been annotated with an [attribute](http://docs.hhvm.com/manual/en/hack.attributes.php) named as specified.  The optional second parameter will restrict the condition further to only include those attributes that include the specified value, matched loosely.  Please note that attribute values may only be strings or integers.

## The Scanner Object

Once a scanner has been built, there are two ways you can access the list of names found.
One is a simple `Map<string,string>` that maps the fully qualified name (including namespace) to the file
in which that name is defined.  The other is an array ready for use by `HH\autoload_set_paths()`
see [this comment](https://github.com/facebook/hhvm/blob/master/hphp/runtime/ext/hh/ext_hh.php#L18-L42) for details.

For example, suppose you have a project structure like this
(this example uses the PSR-4 standard, where `My\Namespace` is associated with `project/src`):

```
- project/
| - src/
  | - Contract/
    | - InterfaceA.php
    | - InterfaceB.php
    | - TypeA.php
  | - Impl/
    | - ClassA.php
    | - ClassB.php
    | - EnumA.php
| - test/
  | - TestThing1.php
  | - TestThing2.php
```

You may build a scanner object and obtain a flat list of all classes/interfaces, or an array ready to configure the autoloader.

```php

$scanner = (new Builder())
    // Scan the entire project
    ->addPath('/path/to/project')
    // Except for the test folder
    ->filterFilenames($n ==> substr($n, 'project/test/') === false)
    ->includeAll()
    ->getScanner();

$scanner->mapNamesToFiles() === Map{
    'My\Namespace\Contract\InterfaceA' => '/path/to/project/src/Contract/InterfaceA.php',
    'My\Namespace\Contract\InterfaceB' => '/path/to/project/src/Contract/InterfaceB.php',
    'My\Namespace\Contract\TypeA' => '/path/to/project/src/Contract/TypeA.php',
    'My\Namespace\Impl\ClassA' => '/path/to/project/src/Impl/ClassA.php',
    'My\Namespace\Impl\ClassB' => '/path/to/project/src/Impl/ClassB.php',
    'My\Namespace\Impl\EnumA' => '/path/to/project/src/Impl/EnumA.php',
}; // true

$scanner->getAutoloadArray() === [
    'class' => [
        'My\Namespace\Contract\InterfaceA' => '/path/to/project/src/Contract/InterfaceA.php',
        'My\Namespace\Contract\InterfaceB' => '/path/to/project/src/Contract/InterfaceB.php',
        'My\Namespace\Impl\ClassA' => '/path/to/project/src/Impl/ClassA.php',
        'My\Namespace\Impl\ClassB' => '/path/to/project/src/Impl/ClassB.php',
        'My\Namespace\Impl\EnumA' => '/path/to/project/src/Impl/EnumA.php',
    ],
    'constant' => [],
    'function' => [],
    'type' => [
        'My\Namespace\Contract\TypeA' => '/path/to/project/src/Contract/TypeA.php',
    ],
]; // true
```

## Reference

### Builder API
-----------
##### addPath(string $path) : this
##### addPaths(Traversable<string> $paths) : this
Add a base path or a list of base paths to be scanned.  Note that all base paths are
scanned recursively.  If you wish to exclude a subdirectory of a base path, use the filename filter (below).

##### filterFilenames(FilenameFilter $filter) : this
Register a filename filter with the builder.  For a file to be loaded and scanned,
all registered filters must return `true` when passed the full path to the file.  Filename filters are useful
for excluding a subdirectory of a base path.

See [Filters.php](https://github.com/HackPack/hack-scanner/blob/master/src/Filters.php) for the signature of the filters.

-----------
##### includeClasses(ClassFilter $filter) : this
##### includeConstants(ConstantFilter $filter) : this
##### includeEnums(EnumFilter $filter) : this
##### includeFunctions(FunctionFilter $filter) : this
##### includeInterfaces(InterfaceFilter $filter) : this
##### includeNewtypes(NewtypeFilter $filter) : this
##### includeTraits(TraitFilter $filter) : this
##### includeTypes(TypeFilter $filter) : this
Instruct the scanner to include a definition type, optionally filtered.

Note that the default callback will allow any definition, making a simple `$builder->includeClasses();` call
include all class definitions.

See [Filters.php](https://github.com/HackPack/hack-scanner/blob/master/src/Filters.php) for the signature of the filters.

-----------
##### filterClasses(ClassFilter $filter) : this
##### filterConstants(ConstantFilter $filter) : this
##### filterEnums(EnumFilter $filter) : this
##### filterFunctions(FunctionFilter $filter) : this
##### filterInterfaces(InterfaceFilter $filter) : this
##### filterNewtypes(NewtypeFilter $filter) : this
##### filterTraits(TraitFilter $filter) : this
##### filterTypes(TypeFilter $filter) : this

Register filter callbacks for the referenced definition type (class, interface, enum, etc.).
The methods may be called many times, where each time an exclusionary filter callback is registered.  For a
definition to be listed, all registered filters must return true.

See [Filters.php](https://github.com/HackPack/hack-scanner/blob/master/src/Filters.php) for the signature of the filters.

-----------
##### includeAll(GenericFilter $filter) : this
##### filterAll(GenericFilter $filter) : this
Calling `includeAll` will include definitions of any type, as long as the optional generic filter passed allows.  Like the other `include` methods, the default filter will allow all definitions.

Calling `filterAll` will remove definitions of all types that do not pass the filter.
