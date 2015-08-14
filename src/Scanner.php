<?hh // strict

namespace HackPack\Scanner;

use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedConstant;
use FredEmmott\DefinitionFinder\ScannedEnum;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedInterface;
use FredEmmott\DefinitionFinder\ScannedNewtype;
use FredEmmott\DefinitionFinder\ScannedTrait;
use FredEmmott\DefinitionFinder\ScannedType;

class Scanner
{
    private Vector<ScannedBasicClass> $classes = Vector{};
    private Vector<ScannedConstant> $constants = Vector{};
    private Vector<ScannedEnum> $enums = Vector{};
    private Vector<ScannedFunction> $functions = Vector{};
    private Vector<ScannedInterface> $interfaces = Vector{};
    private Vector<ScannedNewtype> $newtypes = Vector{};
    private Vector<ScannedTrait> $traits = Vector{};
    private Vector<ScannedType> $types = Vector{};

    public function __construct(
        private Set<string> $files,

        Filter\ClassFilter $classFilter,
        Filter\ConstantFilter $constantFilter,
        Filter\EnumFilter $enumFilter,
        Filter\FunctionFilter $functionFilter,
        Filter\InterfaceFilter $interfaceFilter,
        Filter\NewtypeFilter $newtypeFilter,
        Filter\TraitFilter $traitFilter,
        Filter\TypeFilter $typeFilter,

        Filter\GenericFilter $genericFilter,
    )
    {
        // Apply the specific filter and the generic filter
        $composedFilter = $filter ==> ($object ==> $filter($object) && $genericFilter($object));
        foreach($files as $filename) {
            $parser = FileParser::FromFile($filename);
            $this->classes->addAll($parser->getClasses()->filter($composedFilter($classFilter)));
            $this->constants->addAll($parser->getConstants()->filter($composedFilter($constantFilter)));
            $this->enums->addAll($parser->getEnums()->filter($composedFilter($enumFilter)));
            $this->functions->addAll($parser->getFunctions()->filter($composedFilter($functionFilter)));
            $this->interfaces->addAll($parser->getInterfaces()->filter($composedFilter($interfaceFilter)));
            $this->newtypes->addAll($parser->getNewtypes()->filter($composedFilter($newtypeFilter)));
            $this->traits->addAll($parser->getTraits()->filter($composedFilter($traitFilter)));
            $this->types->addAll($parser->getTypes()->filter($composedFilter($typeFilter)));
        }
    }

    public function getScannedFiles() : \ConstSet<string>
    {
         return $this->files;
    }

    public function getHackFiles() : \ConstSet<string>
    {
        return new Set($this->mapNameToFile()->values());
    }

    public function mapNameToFile() : Map<string,string>
    {
        $scannedDefToPair = $def ==> Pair{$def->getName(), $def->getFileName()};
        $map = Map{};
        $map->addAll($this->classes->map($scannedDefToPair));
        $map->addAll($this->constants->map($scannedDefToPair));
        $map->addAll($this->enums->map($scannedDefToPair));
        $map->addAll($this->functions->map($scannedDefToPair));
        $map->addAll($this->interfaces->map($scannedDefToPair));
        $map->addAll($this->newtypes->map($scannedDefToPair));
        $map->addAll($this->traits->map($scannedDefToPair));
        $map->addAll($this->types->map($scannedDefToPair));

        return $map;
    }

    public function getAutoloadArray() : array<string,array<string,string>>
    {
        $scannedDefToPair = $def ==> Pair{$def->getName(), $def->getFileName()};

        $classes = Map{};
        $classes->addAll($this->classes->map($scannedDefToPair));
        $classes->addAll($this->enums->map($scannedDefToPair));
        $classes->addAll($this->interfaces->map($scannedDefToPair));
        $classes->addAll($this->traits->map($scannedDefToPair));

        $types = Map{};
        $types->addAll($this->newtypes->map($scannedDefToPair));
        $types->addAll($this->types->map($scannedDefToPair));

        $functions = $this->functions->map($scannedDefToPair);
        $constants = $this->constants->map($scannedDefToPair);

        return [
            'class' => $classes->toArray(),
            'constant' => $constants->toArray(),
            'function' => $functions->toArray(),
            'type' => $types->toArray(),
        ];
    }
}
