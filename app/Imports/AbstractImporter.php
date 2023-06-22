<?php

namespace App\Imports;

use App\Imports\Exceptions\SkipRecord;
use App\Imports\Interfaces\ImporterInterface;
use App\Imports\Interfaces\SourceInterface;
use App\Imports\Interfaces\TargetInterface;
use App\Imports\Interfaces\TransformerInterface;
use Exception;

/**
 * Class AbstractImporter.
 */
abstract class AbstractImporter implements ImporterInterface
{
    /**
     * Source connection.
     *
     * @var SourceInterface
     */
    protected SourceInterface $source;

    /**
     * Target connection.
     *
     * @var TargetInterface
     */
    protected TargetInterface $target;

    /**
     * Target connection.
     *
     * @var TransformerInterface|null
     */
    protected ?TransformerInterface $transformer;

    /**
     * Exceptions encountered during last import.
     *
     * @var array
     */
    protected array $exceptions = [];

    /**
     * Last transformed record.
     *
     * @var array|null
     */
    protected ?array $lastRecord = null;

    /**
     * Bad records encountered during last import.
     *
     * @var array
     */
    protected array $badRecords = [];

    /**
     * Record that were skipped during last import.
     *
     * @var array
     */
    protected array $skippedRecords = [];

    /**
     * AbstractImporter constructor.
     *
     * @param SourceInterface $source
     * @param TargetInterface $target
     * @param TransformerInterface|null $transformer
     */
    public function __construct(
        SourceInterface $source,
        TargetInterface $target,
        ?TransformerInterface $transformer = null,
    ) {
        $this->source = $source;
        $this->target = $target;
        $this->transformer = $transformer;
    }

    /**
     * Import records from source connection to target.
     *
     * @return int
     * @throws Exception
     */
    public function import(): int
    {
        $this->exceptions = [];
        $this->badRecords = [];

        $this->source->open();

        $imported = 0;

        while ($record = $this->source->next()) {
            try {
                $data = $this->transformer
                    ? $this->transformer->transform($record)
                    : $record;

                $this->before($data);

                if ($this->target->insert($data)) {
                    $this->after($data);
                    $imported++;
                }

                $this->lastRecord = $data;
            } catch (SkipRecord) {
                // @phpstan-ignore-next-line
                $this->skippedRecords[] = $data ?? $record;
            } catch (Exception $exception) {
                $this->exceptions[] = $exception;
                $this->badRecords[] = $data ?? $record;
            }
        }

        $this->source->close();

        return $imported;
    }

    /**
     * Perform any additional actions before importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function before(array &$record): void
    {
        //
    }

    /**
     * Perform any additional actions after importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function after(array &$record): void
    {
        //
    }

    /**
     * Exceptions encountered during last import.
     *
     * @return array
     */
    public function exceptions(): array
    {
        return $this->exceptions ?? [];
    }

    /**
     * Last transformed record.
     *
     * @return array|null
     */
    public function lastRecord(): ?array
    {
        return $this->lastRecord;
    }

    /**
     * Bad records encountered during last import.
     *
     * @return array
     */
    public function badRecords(): array
    {
        return $this->badRecords ?? [];
    }

    /**
     * Records that were skipped during last import.
     *
     * @return array
     */
    public function skippedRecords(): array
    {
        return $this->skippedRecords ?? [];
    }
}
