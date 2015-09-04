<?hh // strict

namespace HackPack\Scanner;

use HackPack\Scanner\Contract\Scanner;

newtype FilterContainer<T> = shape(
    'add' => Vector<T>,
    'remove' => Vector<T>
);

newtype FilterList = shape(
    'class' => FilterContainer<Filter\ClassFilter>,
    'constant' => FilterContainer<Filter\ConstantFilter>,
    'enum' => FilterContainer<Filter\EnumFilter>,
    'function' => FilterContainer<Filter\FunctionFilter>,
    'interface' => FilterContainer<Filter\InterfaceFilter>,
    'newtype' => FilterContainer<Filter\NewtypeFilter>,
    'trait' => FilterContainer<Filter\TraitFilter>,
    'type' => FilterContainer<Filter\TypeFilter>,
    'generic' => FilterContainer<Filter\GenericFilter>,
);

class Builder
{
    private Set<string> $baseDirs = Set{};
    private Vector<Filter\FilenameFilter> $filenameFilters = Vector{};
    private FilterList $filters = shape(
        'class' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'constant' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'enum' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'function' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'interface' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'newtype' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'trait' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'type' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
        'generic' => shape(
            'add' => Vector{},
            'remove' => Vector{}
        ),
    );

    private (function(Traversable<string>, Filter\FilterSet):Scanner) $scannerGenerator;

    public function __construct(
        ?(function(Traversable<string>, Filter\FilterSet):Scanner) $scannerGenerator = null
    )
    {
        if($scannerGenerator === null) {
             $this->scannerGenerator = ($list, $set) ==> new \HackPack\Scanner\Scanner($list, $set);
        } else {
            $this->scannerGenerator = $scannerGenerator;
        }
    }

    public function addPath(string $path) : this
    {
        $this->baseDirs->add($path);
        return $this;
    }

    public function addPaths(Traversable<string> $paths) : this
    {
        $this->baseDirs->addAll($paths);
        return $this;
    }

    public function filterFilenames(Filter\FilenameFilter $f) : this
    {
        $this->filenameFilters->add($f);
        return $this;
    }

    public function inclueAll(Filter\GenericFilter $f = $x ==> true) : this
    {
        $this->filters['generic']['add']->add($f);
        return $this;
    }

    public function filterAll(Filter\GenericFilter $f) : this
    {
        $this->filters['generic']['remove']->add($f);
        return $this;
    }

    public function includeClasses(Filter\ClassFilter $f) : this
    {
        $this->filters['class']['add']->add($f);
        return $this;
    }

    public function includeConstants(Filter\ConstantFilter $f) : this
    {
        $this->filters['constant']['add']->add($f);
        return $this;
    }

    public function includeEnums(Filter\EnumFilter $f) : this
    {
        $this->filters['enum']['add']->add($f);
        return $this;
    }

    public function includeFunctions(Filter\FunctionFilter $f) : this
    {
        $this->filters['function']['add']->add($f);
        return $this;
    }

    public function includeInterfaces(Filter\InterfaceFilter $f) : this
    {
        $this->filters['interface']['add']->add($f);
        return $this;
    }

    public function includeNewtypes(Filter\NewtypeFilter $f) : this
    {
        $this->filters['newtype']['add']->add($f);
        return $this;
    }

    public function includeTraits(Filter\TraitFilter $f) : this
    {
        $this->filters['trait']['add']->add($f);
        return $this;
    }

    public function includeTypes(Filter\TypeFilter $f) : this
    {
        $this->filters['type']['add']->add($f);
        return $this;
    }

    public function filterClasses(Filter\ClassFilter $f) : this
    {
        $this->filters['class']['remove']->add($f);
        return $this;
    }

    public function filterConstants(Filter\ConstantFilter $f) : this
    {
        $this->filters['constant']['remove']->add($f);
        return $this;
    }

    public function filterEnums(Filter\EnumFilter $f) : this
    {
        $this->filters['enum']['remove']->add($f);
        return $this;
    }

    public function filterFunctions(Filter\FunctionFilter $f) : this
    {
        $this->filters['function']['remove']->add($f);
        return $this;
    }

    public function filterInterfaces(Filter\InterfaceFilter $f) : this
    {
        $this->filters['interface']['remove']->add($f);
        return $this;
    }

    public function filterNewtypes(Filter\NewtypeFilter $f) : this
    {
        $this->filters['newtype']['remove']->add($f);
        return $this;
    }

    public function filterTraits(Filter\TraitFilter $f) : this
    {
        $this->filters['trait']['remove']->add($f);
        return $this;
    }

    public function filterTypes(Filter\TypeFilter $f) : this
    {
        $this->filters['type']['remove']->add($f);
        return $this;
    }

    public function getScanner() : Contract\Scanner
    {
        $gen = $this->scannerGenerator;
        return $gen(
            $this->filesToScan(),
            shape(
                'class' => $this->buildFilter($this->filters['class'], $x ==> false),
                'constant' => $this->buildFilter($this->filters['constant'], $x ==> false),
                'enum' => $this->buildFilter($this->filters['enum'], $x ==> false),
                'function' => $this->buildFilter($this->filters['function'], $x ==> false),
                'interface' => $this->buildFilter($this->filters['interface'], $x ==> false),
                'newtype' => $this->buildFilter($this->filters['newtype'], $x ==> false),
                'trait' => $this->buildFilter($this->filters['trait'], $x ==> false),
                'type' => $this->buildFilter($this->filters['type'], $x ==> false),
            ),
        );
    }

    private function filesToScan() : Set<string>
    {
        $files = Set{};
        // Canonicalize and ensure all paths are readable
        $canonicalDirs = $this->baseDirs->map($name ==> {
            $rp = realpath($name);
            if($rp === false || ! is_readable($rp)) {
                // Need to be able to find and read the path
                return null;
            }

            // If user supplied path to a file, just add it to the list
            if(is_file($rp)) {
                $files->add($rp);
                return null;
            }

            // Path is to a directory
            return $rp;
        })->filter($path ==> is_string($path));

        // Recursively scan all directories for all readable files
        array_walk($canonicalDirs, $dir ==> {
            $dIterator = new \RecursiveDirectoryIterator($dir);
            foreach($dIterator as $finfo) {
                if($finfo->isFile() && $finfo->isReadable()){
                    $files->add($finfo->getPath());
                }
            }
        });

        // Filter the files based on user preferences
        $composedFilter = $filename ==> array_reduce(
            $this->filenameFilters,
            ($result, $filter) ==> $result && $filter($filename),
            // Default accept the file
            true
        );
        return $files->filter($composedFilter);
    }


    private function buildFilter<T>(FilterContainer<Filter\Filter<T>> $filters, Filter\Filter<T> $default) : Filter\Filter<T>
    {
        if($filters['add']->isEmpty()) {
            return $default;
        }

        // The definition must be added generically or specifically
        // The definition must pass all generic and specific filters
        return $n ==>
            (
                array_reduce($filters['add'], ($result, $filter) ==> $result || $filter($n), false) ||
                array_reduce($this->filters['generic']['add'], ($result, $filter) ==> $result || $filter($n), false)
            ) && (
                array_reduce($filters['remove'], ($result, $filter) ==> $result && $filter($n), true) &&
                array_reduce($this->filters['generic']['remove'], ($result, $filter) ==> $result && $filter($n), true)
            )
            ;
    }
}
