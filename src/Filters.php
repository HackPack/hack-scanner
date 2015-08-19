<?hh // strict

namespace HackPack\Scanner\Filter;

use FredEmmott\DefinitionFinder\ScannedBase;
use FredEmmott\DefinitionFinder\ScannedBasicClass;
use FredEmmott\DefinitionFinder\ScannedConstant;
use FredEmmott\DefinitionFinder\ScannedEnum;
use FredEmmott\DefinitionFinder\ScannedFunction;
use FredEmmott\DefinitionFinder\ScannedInterface;
use FredEmmott\DefinitionFinder\ScannedNewtype;
use FredEmmott\DefinitionFinder\ScannedTrait;
use FredEmmott\DefinitionFinder\ScannedType;

type FilenameFilter = (function(string):bool);

type Filter<T> = (function(T):bool);

type ClassFilter = (function(ScannedBasicClass):bool);
type ConstantFilter = (function(ScannedConstant):bool);
type EnumFilter = (function(ScannedEnum):bool);
type FunctionFilter = (function(ScannedFunction):bool);
type InterfaceFilter = (function(ScannedInterface):bool);
type NewtypeFilter = (function(ScannedNewtype):bool);
type TraitFilter = (function(ScannedTrait):bool);
type TypeFilter = (function(ScannedType):bool);

type GenericFilter = (function(ScannedBase):bool);
