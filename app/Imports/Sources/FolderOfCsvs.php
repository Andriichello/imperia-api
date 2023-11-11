<?php

namespace App\Imports\Sources;

use App\Imports\Interfaces\SourceInterface;
use Exception;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
use TypeError;

/**
 * Class FolderOfCsvs.
 */
class FolderOfCsvs implements SourceInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string
     */
    protected string $delimiter;

    /**
     * @var bool
     */
    protected bool $withHeaders;

    /**
     * @var array
     */
    protected array $files;

    /**
     * @var int
     */
    protected int $position;

    /**
     * @var CsvSource|null
     */
    protected ?CsvSource $source;

    /**
     * CsvSource constructor.
     *
     * @param string $path
     * @param bool $withHeaders
     * @param string $delimiter
     *
     * @SuppressWarnings(PHPMD)
     */
    public function __construct(
        string $path,
        bool $withHeaders = true,
        string $delimiter = ','
    ) {
        $this->path = Str::of($path)
            ->finish(DIRECTORY_SEPARATOR)
            ->value();

        $this->withHeaders = $withHeaders;
        $this->delimiter = $delimiter;
    }

    /**
     * CsvSource destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Open file specified in path.
     *
     * @return static
     * @throws Exception
     */
    public function open(): static
    {
        $files = scandir($this->path);

        if ($files === false) {
            throw new Exception('Failed to open folder: ' . $this->path);
        }

        foreach ($files as $file) {
            if (!str_ends_with($file, '.csv')) {
                continue;
            }

            $this->files[] = $this->path . $file;
        }

        if (empty($this->files)) {
            throw new Exception('No .csv files were found in: ' . $this->path);
        }

        (new ConsoleOutput())->writeln('Opening file: ' . $this->files[0]);
        $this->source = new CsvSource($this->files[0], $this->withHeaders, $this->delimiter);
        $this->source->open();

        $this->position = 0;

        return $this;
    }

    /**
     * Close currently opened source path.
     *
     * @return static
     */
    public function close(): static
    {
        try {
            if (isset($this->source)) {
                $this->source->close();
            }
        } catch (TypeError) {
            //
        }

        return $this;
    }

    /**
     * Returns an array of headers (first row of the file).
     *
     * @return array|null
     */
    public function headers(): ?array
    {
        if (!isset($this->source)) {
            return false;
        }

        return $this->source->headers();
    }

    /**
     * Returns current file number.
     *
     * @return int|null
     */
    public function position(): ?int
    {
        if (!isset($this->source)) {
            return null;
        }

        return $this->position;
    }

    /**
     * Returns true if last line was read.
     *
     * @return bool|null
     */
    public function ended(): ?bool
    {
        if (!isset($this->source)) {
            return null;
        }

        return $this->source->ended();
    }

    /**
     * Returns last read row.
     *
     * @return array|false
     */
    public function last(): array|false
    {
        if (!isset($this->source)) {
            return false;
        }

        return $this->source->last();
    }

    /**
     * Read and return next line.
     *
     * @return array|false
     * @throws Exception
     */
    public function next(): array|false
    {
        if (!isset($this->source)) {
            return false;
        }

        $result = $this->source->next();

        if ($result === false) {
            $this->source->close();

            if (($this->position + 1) < count($this->files)) {
                $this->position++;

                (new ConsoleOutput())->writeln('Opening file: ' . $this->files[$this->position]);
                $this->source = new CsvSource($this->files[$this->position], $this->withHeaders, $this->delimiter);
                $this->source->open();

                return $this->source->next();
            }
        }

        return $result;
    }
}
