<?hh // strict

namespace HackPack\Scanner\Filter;

use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedConstant;
use FredEmmott\DefinitionFinder\ScannedEnum;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedBase;
use FredEmmott\DefinitionFinder\ScannedInterface;
use FredEmmott\DefinitionFinder\ScannedNewtype;
use FredEmmott\DefinitionFinder\ScannedTrait;
use FredEmmott\DefinitionFinder\ScannedType;

type Filter<-T> = (function(T):bool);

type ClassFilter = Filter<ScannedBasicClass>;
type ConstantFilter = Filter<ScannedConstant>;
type EnumFilter = Filter<ScannedEnum>;
type FilenameFilter = Filter<string>;
type FilterSet = shape(
    'class' => ClassFilter,
    'constant' => ConstantFilter,
    'enum' => EnumFilter,
    'function' => FunctionFilter,
    'interface' => InterfaceFilter,
    'newtype' => NewtypeFilter,
    'trait' => TraitFilter,
    'type' => TypeFilter,
);
type FunctionFilter = Filter<ScannedFunction>;
type GenericFilter = Filter<ScannedBase>;
type InterfaceFilter = Filter<ScannedInterface>;
type NewtypeFilter = Filter<ScannedNewtype>;
type TraitFilter = Filter<ScannedTrait>;
type TypeFilter = Filter<ScannedType>;
