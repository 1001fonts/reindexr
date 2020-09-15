<?php
declare(strict_types=1);

namespace Basster\Reindexr\Input;

use Basster\Reindexr\Input\Exception\InvalidPartitionTypeException;
use Basster\Reindexr\Input\Exception\UnsupportedPartitionTypeException;
use Basster\Reindexr\PartitionType;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class ReindexConfig.
 *
 * @psalm-immutable
 */
final class ReindexConfig implements \JsonSerializable
{
    public string $prefix;
    public PartitionType $from;
    public PartitionType $to;
    public bool $includeCurrent;

    /**
     * ReindexConfig constructor.
     */
    private function __construct(string $prefix, PartitionType $from, PartitionType $to, bool $includeCurrent = false)
    {
        $this->prefix = $prefix;
        $this->from = $from;
        $this->to = $to;
        $this->includeCurrent = $includeCurrent;
    }

    public static function create(string $prefix, PartitionType $from, PartitionType $to, bool $includeCurrent = false): self
    {
        return new self($prefix, $from, $to, $includeCurrent);
    }

    public static function createFromInput(InputInterface $input): self
    {
        /** @var string $prefix */
        $prefix = $input->getArgument('prefix');
        $from = self::getFrom($input);
        $to = self::getTo($input);

        return self::create(
            $prefix,
            $from,
            $to,
            (bool) $input->getOption('include-current')
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'prefix' => $this->prefix,
            'from' => (string) $this->from,
            'to' => (string) $this->to,
            'include-current' => $this->includeCurrent,
        ];
    }

    private static function getFrom(InputInterface $input): PartitionType
    {
        $postParseValidator = static function (PartitionType $type): void {
            if ($type->equals(PartitionType::YEARLY())) {
                throw new UnsupportedPartitionTypeException((string) $type, 'to');
            }
        };

        return self::parsePartitionType($input, 'from', $postParseValidator);
    }

    private static function getTo(InputInterface $input): PartitionType
    {
        $postParseValidator = static function (PartitionType $type): void {
            if ($type->equals(PartitionType::DAILY())) {
                throw new UnsupportedPartitionTypeException((string) $type, 'to');
            }
        };

        return self::parsePartitionType($input, 'to', $postParseValidator);
    }

    /**
     * @param \Closure(PartitionType):void $postParseValidator
     */
    private static function parsePartitionType(InputInterface $input, string $argument, \Closure $postParseValidator): PartitionType
    {
        /** @var string $argumentValue */
        $argumentValue = $input->getArgument($argument);

        try {
            $partitionType = new PartitionType($argumentValue);
            $postParseValidator($partitionType);
        } catch (\UnexpectedValueException | UnsupportedPartitionTypeException $ex) {
            throw new InvalidPartitionTypeException($argumentValue, $argument, $ex);
        }

        return $partitionType;
    }
}
