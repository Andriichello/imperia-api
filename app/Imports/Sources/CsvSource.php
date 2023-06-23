<?php

namespace App\Imports\Sources;

use App\Imports\Interfaces\SourceInterface;
use Exception;
use TypeError;

/**
 * Class CsvSource.
 */
class CsvSource implements SourceInterface
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
     * @var resource|false
     */
    protected $handle;

    /**
     * @var int
     */
    protected int $position;

    /**
     * @var bool
     */
    protected bool $withHeaders;

    /**
     * @var array
     */
    protected array $headers;

    /**
     * @var array|false
     */
    protected array|false $line;

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
        $this->path = $path;
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
        $this->handle = fopen($this->path, 'r');
        $this->position = 0;

        if (!$this->handle) {
            throw new Exception('Failed to open file: ' . $this->path);
        }

        if ($this->withHeaders) {
            $this->headers = fgetcsv($this->handle, 0, $this->delimiter);
        }

        return $this;
    }

    /**
     * Close file specified in path.
     *
     * @return static
     */
    public function close(): static
    {
        try {
            if ($this->handle) {
                fclose($this->handle);
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
        if (!$this->handle) {
            return null;
        }

        return $this->headers ?? null;
    }

    /**
     * Returns current row number.
     *
     * @return int|null
     */
    public function position(): ?int
    {
        if (!$this->handle) {
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
        if (!$this->handle) {
            return null;
        }

        return feof($this->handle);
    }

    /**
     * Returns last read row.
     *
     * @return array|false
     */
    public function last(): array|false
    {
        if (!isset($this->line)) {
            return false;
        }

        if ($this->withHeaders) {
            $line = [];

            foreach ($this->headers as $index => $header) {
                $line[$header] = data_get($this->line, $index);
            }

            return $line;
        }

        return $this->line;
    }

    /**
     * Read and return next line.
     *
     * @return array|false
     */
    public function next(): array|false
    {
        if (!$this->handle || $this->ended()) {
            return false;
        }

        $this->line = fgetcsv($this->handle, 0, $this->delimiter);
        $this->position++;

        return $this->last();
    }
}
