<?hh // strict

namespace HackPack\Scanner;

class Builder
{
    private Set<string> $baseDirs = Set{};
    private Filter\FilenameFilter $filenameFilter;

    private Filter\ClassFilter $classFilter;
    private Filter\ConstantFilter $constantFilter;
    private Filter\EnumFilter $enumFilter;
    private Filter\FunctionFilter $functionFilter;
    private Filter\InterfaceFilter $interfaceFilter;
    private Filter\NewtypeFilter $newtypeFilter;
    private Filter\TraitFilter $traitFilter;
    private Filter\TypeFilter $typeFilter;

    private Filter\GenericFilter $genericFilter;

    public function __construct()
    {
        $accept = $p ==> true;
        $reject = $c ==> false;

        // Default reject individual definitions
        $this->classFilter = $reject;
        $this->constantFilter = $reject;
        $this->enumFilter = $reject;
        $this->functionFilter = $reject;
        $this->interfaceFilter = $reject;
        $this->newtypeFilter = $reject;
        $this->traitFilter = $reject;
        $this->typeFilter = $reject;

        // Default accept definitions generically
        $this->genericFilter = $accept;

        // Default scan all files
        $this->filenameFilter = $accept;
    }

    public function addPaths(Traversable<string> $paths) : this
    {
        $this->baseDirs->addAll($paths);
        return $this;
    }

    public function filterPaths(Filter\FilenameFilter $f) : this
    {
        $this->filenameFilter = $f;
        return $this;
    }

    public function inclueAll() : this
    {
        $accept = $c ==> true;
        $this->classFilter = $accept;
        $this->constantFilter = $accept;
        $this->enumFilter = $accept;
        $this->functionFilter = $accept;
        $this->interfaceFilter = $accept;
        $this->newtypeFilter = $accept;
        $this->traitFilter = $accept;
        $this->typeFilter = $accept;
        return $this;
    }

    public function filterAll(Filter\GenericFilter $f) : this
    {
        $this->genericFilter = $f;
        return $this;
    }

    public function includeClasses() : this
    {
        $this->classFilter = $c ==> true;
        return $this;
    }

    public function includeConstants() : this
    {
        $this->constantFilter = $c ==> true;
        return $this;
    }

    public function includeEnums() : this
    {
        $this->enumFilter = $c ==> true;
        return $this;
    }

    public function includeFunctions() : this
    {
        $this->functionFilter = $c ==> true;
        return $this;
    }

    public function includeInterfaces() : this
    {
        $this->interfaceFilter = $c ==> true;
        return $this;
    }

    public function includeNewtypes() : this
    {
        $this->newtypeFilter = $c ==> true;
        return $this;
    }

    public function includeTraits() : this
    {
        $this->traitFilter = $c ==> true;
        return $this;
    }

    public function includeTypes() : this
    {
        $this->typeFilter = $c ==> true;
        return $this;
    }

    public function includeAndFilterClasses(Filter\ClassFilter $f) : this
    {
        $this->classFilter = $f;
        return $this;
    }

    public function includeAndFilterConstants(Filter\ConstantFilter $f) : this
    {
        $this->constantFilter = $f;
        return $this;
    }

    public function includeAndFilterEnums(Filter\EnumFilter $f) : this
    {
        $this->enumFilter = $f;
        return $this;
    }

    public function includeAndFilterFunctions(Filter\FunctionFilter $f) : this
    {
        $this->functionFilter = $f;
        return $this;
    }

    public function includeAndFilterInterfaces(Filter\InterfaceFilter $f) : this
    {
        $this->interfaceFilter = $f;
        return $this;
    }

    public function includeAndFilterNewtypes(Filter\NewtypeFilter $f) : this
    {
        $this->newtypeFilter = $f;
        return $this;
    }

    public function includeAndFilterTraits(Filter\TraitFilter $f) : this
    {
        $this->traitFilter = $f;
        return $this;
    }

    public function includeAndFilterTypes(Filter\TypeFilter $f) : this
    {
        $this->typeFilter = $f;
        return $this;
    }

    public function getScanner() : Scanner
    {
        return new Scanner(
            $this->filesToScan(),
            $this->classFilter,
            $this->constantFilter,
            $this->enumFilter,
            $this->functionFilter,
            $this->interfaceFilter,
            $this->newtypeFilter,
            $this->traitFilter,
            $this->typeFilter,
            $this->genericFilter,
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
            /* HH_FIXME[2049] No HHI */
            $dIterator = new \RecursiveDirectoryIterator($dir);
            foreach($dIterator as $finfo) {
                if($finfo->isFile() && $finfo->isReadable()){
                    $files->add($finfo->getPath());
                }
            }
        });

        // Filter the files based on user preferences
        return $files->filter($this->filenameFilter);
    }
}
