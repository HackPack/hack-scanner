<?hh // strict

namespace HackPack\Scanner\Contract;

interface Scanner
{
    public function getScannedFiles() : \ConstSet<string>;
    public function getHackFiles() : \ConstSet<string>;
    public function mapNameToFile() : Map<string,string>;
    public function getAutoloadArray() : array<string,array<string,string>>;
}
