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
        $this->assertSame($first, $ping->getHost());

        $second = 'www.apple.com';
        $ping->setHost($second)->setPort(80);
        $this->assertSame($second, $ping->getHost());
        $this->assertSame(80, $ping->getPort());
    }

    public function testTtl(): void
    {
        $first = 220;
        $ping = new Ping($this->reachable_host, $first);
        $this->assertSame($first, $ping->getTtl());

        $second = 128;
        $ping->setTtl($second);
        $this->assertSame($second, $ping->getTtl());
    }

    /**
     * @throws Exception
     */
    public function testTimeout(): void
    {
        $timeout = 5;
        $startTime = microtime(true);
        $ping = new Ping($this->unreachable_host, 255, $timeout);
        $ping->ping('exec');
        $time = floor(microtime(true) - $startTime);
        $this->assertLessThanOrEqual($timeout, $time);
    }

    /**
     * @throws Exception
     */
    public function testLowLatencyHost(): void
    {
        $low_latency = $this->low_latency_host;
        $ping = new Ping($low_latency);
//        $ping->ping(); // method = exec
        $latency = $ping->ping();
        $this->assertGreaterThan(0, $latency);
    }

    public function testPort(): void
    {
        $port = 2222;
        $ping = new Ping($this->reachable_host);
        $ping->setPort($port);
        $this->assertSame($port, $ping->getPort());
    }

    /**
     * @throws Exception
     */
    public function testGetCommandOutput(): void
    {
        $ping = new Ping('127.0.0.1');
        $latency = $ping->ping();
        $this->assertNotNull($ping->getCommandOutput());
    }

    /**
     * @throws Exception
     */
    public function testIpAddress(): void
    {
        $ping = new Ping('127.0.0.1');
        $ping->ping();
        $this->assertSame('127.0.0.1', $ping->getIpAddress());
    }

    /**
     * @throws Exception
     */
    public function testPingExec(): void
    {
        $ping = new Ping($this->reachable_host);
        $latency = $ping->ping();
        $this->assertNotFalse($latency);

        $ping->setHost($this->unreachable_host);
        $latency = $ping->ping();
        $this->assertNull($latency);
    }

    /**
     * @throws Exception
     */
    public function testPingFsockopen(): void
    {
        $ping = new Ping($this->reachable_host);
        $latency = $ping->ping('fsockopen');
        $this->assertNotFalse($latency);

        $ping = new Ping($this->unreachable_host);
        $latency = $ping->ping('fsockopen');
        $this->assertNull($latency);
    }

    /**
     * These tests require sudo/root so socket can be opened.
     *
     * @throws Exception
     */
    public function testPingSocket(): void
    {
        $this->assertTrue(true);

        // Note: Sudo permission is required to execute PHPUnit tests.
        // $ping = new Ping($this->reachable_host);
        // $latency = $ping->ping('socket');
        // static::assertNotFalse($latency);

        // $ping = new Ping($this->unreachable_host);
        // $latency = $ping->ping('socket');
        // static::assertNull($latency);
    }
}
