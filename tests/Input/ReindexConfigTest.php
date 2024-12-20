<?php
declare(strict_types=1);

namespace Maxfonts\Reindexr\Tests\Input;

use Maxfonts\Reindexr\Input\Exception\InvalidPartitionTypeException;
use Maxfonts\Reindexr\Input\ReindexConfig;
use Maxfonts\Reindexr\PartitionType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * @internal
 */
final class ReindexConfigTest extends TestCase
{
    /**
     * @test
     */
    public function createFromInput(): void
    {
        $input = $this->createInput('daily', 'monthly');

        $config = ReindexConfig::createFromInput($input);
        self::assertSame('foobar', $config->prefix);
        self::assertTrue(PartitionType::DAILY()->equals($config->from));
        self::assertTrue(PartitionType::MONTHLY()->equals($config->to));
    }

    /**
     * @test
     * @dataProvider provideInvalidFromArguments
     */
    public function createFromInputWithInvalidFrom(string $input): void
    {
        $this->expectException(InvalidPartitionTypeException::class);
        $this->expectExceptionMessage("\"{$input}\" is not a valid partition type for \"from\"");

        $input = $this->createInput($input, (string) PartitionType::MONTHLY());
        ReindexConfig::createFromInput($input);
    }

    public function provideInvalidFromArguments(): iterable
    {
        yield 'bullshit' => ['foobar'];
        yield 'from yearly is invalid' => [(string) PartitionType::YEARLY()];
    }

    /**
     * @test
     * @dataProvider provideInvalidToArguments
     */
    public function createFromInputWithInvalidTo(string $input): void
    {
        $this->expectException(InvalidPartitionTypeException::class);
        $this->expectExceptionMessage("\"{$input}\" is not a valid partition type for \"to\"");

        $input = $this->createInput((string) PartitionType::DAILY(), $input);
        ReindexConfig::createFromInput($input);
    }

    public function provideInvalidToArguments(): iterable
    {
        yield 'bullshit' => ['foobar'];
        yield 'to daily is invalid' => [(string) PartitionType::DAILY()];
    }

    /**
     * @test
     */
    public function includeCurrentIsFalseByDefault(): void
    {
        $input = $this->createInput('daily', 'monthly');
        $config = ReindexConfig::createFromInput($input);
        self::assertFalse($config->includeCurrent);
    }

    /**
     * @test
     */
    public function createConfigWithIncludeCurrent(): void
    {
        $input = new ArrayInput([
            'prefix' => 'foobar',
            'from' => 'daily',
            'to' => 'monthly',
            '--include-current' => true,
        ], $this->getInputDefinition());

        $config = ReindexConfig::createFromInput($input);
        self::assertTrue($config->includeCurrent);
    }

    private function getInputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument('prefix', InputArgument::REQUIRED),
            new InputArgument('from', InputArgument::REQUIRED),
            new InputArgument('to', InputArgument::REQUIRED),
            new InputOption('include-current', null, InputOption::VALUE_REQUIRED, 'include current "to" partition', false),
        ]);
    }

    private function createInput(string $from, string $to): ArrayInput
    {
        return new ArrayInput([
            'prefix' => 'foobar',
            'from' => $from,
            'to' => $to,
        ], $this->getInputDefinition());
    }
}
