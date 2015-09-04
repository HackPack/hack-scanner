<?hh // strict

namespace HackPack\Scanner\Test\Double;

class Scanner implements \HackPack\Scanner\Contract\Scanner
{
    public function __construct(private Set<string> $files)
    {
    }

    public function getScannedFiles() : \ConstSet<string>
    {
        return $this->files;
    }

    public function getHackFiles() : \ConstSet<string>
    {
        throw new \RuntimeException('Scanner stub does not actually scan files.');
    }

    public function mapNameToFile() : Map<string,string>
    {
        throw new \RuntimeException('Scanner stub does not actually scan files.');
    }

    public function getAutoloadArray() : array<string,array<string,string>>
    {
        throw new \RuntimeException('Scanner stub does not actually scan files.');
    }
}
