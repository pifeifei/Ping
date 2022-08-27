<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pifeifei\Ping;

/**
 * @internal
 * @coversNothing
 */
final class PingTest extends TestCase
{
    private string $reachable_host = 'www.baidu.com';
    private string $unreachable_host = '254.254.254.254';
    private string $low_latency_host = '127.0.0.1';

    public function testHost(): void
    {
        $first = $this->reachable_host;
        $ping = new Ping($first);
        static::assertSame($first, $ping->getHost());

        $second = 'www.apple.com';
        $ping->setHost($second)->setPort(80);
        static::assertSame($second, $ping->getHost());
        static::assertSame(80, $ping->getPort());
    }

    public function testTtl(): void
    {
        $first = 220;
        $ping = new Ping($this->reachable_host, $first);
        static::assertSame($first, $ping->getTtl());

        $second = 128;
        $ping->setTtl($second);
        static::assertSame($second, $ping->getTtl());
    }

    public function testTimeout(): void
    {
        $timeout = 5;
        $startTime = microtime(true);
        $ping = new Ping($this->unreachable_host, 255, $timeout);
        $ping->ping('exec');
        $time = floor(microtime(true) - $startTime);
        static::assertLessThanOrEqual($timeout, $time);
    }

    public function testLowLatencyHost(): void
    {
        $low_latency = $this->low_latency_host;
        $ping = new Ping($low_latency);
//        $ping->ping(); // method = exec
        $latency = $ping->ping();
        static::assertGreaterThan(0, $latency);
    }

    public function testPort(): void
    {
        $port = 2222;
        $ping = new Ping($this->reachable_host);
        $ping->setPort($port);
        static::assertSame($port, $ping->getPort());
    }

    public function testGetCommandOutput(): void
    {
        $ping = new Ping('127.0.0.1');
        $latency = $ping->ping('exec');
        static::assertNotNull($ping->getCommandOutput());
    }

    public function testIpAddress(): void
    {
        $ping = new Ping('127.0.0.1');
        $ping->ping('exec');
        static::assertSame('127.0.0.1', $ping->getIpAddress());
    }

    public function testPingExec(): void
    {
        $ping = new Ping($this->reachable_host);
        $latency = $ping->ping('exec');
        static::assertNotFalse($latency);

        $ping->setHost($this->unreachable_host);
        $latency = $ping->ping('exec');
        static::assertNull($latency);
    }

    public function testPingFsockopen(): void
    {
        $ping = new Ping($this->reachable_host);
        $latency = $ping->ping('fsockopen');
        static::assertNotFalse($latency);

        $ping = new Ping($this->unreachable_host);
        $latency = $ping->ping('fsockopen');
        static::assertNull($latency);
    }

    /**
     * These tests require sudo/root so socket can be opened.
     *
     * @throws Exception
     */
    public function testPingSocket(): void
    {
        $ping = new Ping($this->reachable_host);
        $latency = $ping->ping('socket');
        static::assertNotFalse($latency);

        $ping = new Ping($this->unreachable_host);
        $latency = $ping->ping('socket');
        static::assertNull($latency);
    }
}
