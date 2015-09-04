<?hh // strict

namespace HackPack\Scanner\Filter;

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
