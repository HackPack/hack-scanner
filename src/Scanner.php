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

class Scanner implements Contract\Scanner
{
    private Vector<ScannedBasicClass> $classes = Vector{};
    private Vector<ScannedConstant> $constants = Vector{};
    private Vector<ScannedEnum> $enums = Vector{};
    private Vector<ScannedFunction> $functions = Vector{};
    private Vector<ScannedInterface> $interfaces = Vector{};
    private Vector<ScannedNewtype> $newtypes = Vector{};
    private Vector<ScannedTrait> $traits = Vector{};
    private Vector<ScannedType> $types = Vector{};

    private Set<string> $files;
    public function __construct(
        Traversable<string> $files,
        private Filter\FilterSet $filters,
    )
    {
        $this->files = new Set($files);
        foreach($this->files as $filename) {
            $parser = FileParser::FromFile($filename);
            $this->classes->addAll($parser->getClasses()->filter($filters['class']));
            $this->constants->addAll($parser->getConstants()->filter($filters['constant']));
            $this->enums->addAll($parser->getEnums()->filter($filters['enum']));
            $this->functions->addAll($parser->getFunctions()->filter($filters['function']));
            $this->interfaces->addAll($parser->getInterfaces()->filter($filters['interface']));
            $this->newtypes->addAll($parser->getNewtypes()->filter($filters['newtype']));
            $this->traits->addAll($parser->getTraits()->filter($filters['trait']));
            $this->types->addAll($parser->getTypes()->filter($filters['type']));
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
