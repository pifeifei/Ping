<?php

declare(strict_types=1);

/**
 * Ping for PHP.
 *
 * This class pings a host.
 *
 * The ping() method pings a server using 'exec', 'socket', or 'fsockopen',
 * and returns FALSE if the server is unreachable within the given ttl/timeout,
 * or the latency in milliseconds if the server is reachable.
 *
 * Quick Start:
 *
 * @code
 *   include 'path/to/Ping/JJG/Ping.php';
 *   use \JJG\Ping as Ping;
 *   $ping = new Ping('www.example.com');
 *   $latency = $ping->ping();
 * @endcode
 *
 * @author Jeff Geerling.
 */

namespace Pifeifei;

use Exception;
use InvalidArgumentException;

class Ping
{
    private string $host;
    private int $ttl;
    private int $timeout;
    private int $port = 80;
    private string $data = 'Ping';
    private string $commandOutput;

    /**
     * Called when the Ping object is created.
     *
     * @param string $host the host to be pinged
     * @param int $ttl
     *                 Time-to-live (TTL) (You may get a 'Time to live exceeded' error if this
     *                 value is set too low. The TTL value indicates the scope or range in which
     *                 a packet may be forwarded. By convention:
     *                 - 0 = same host
     *                 - 1 = same subnet
     *                 - 32 = same site
     *                 - 64 = same region
     *                 - 128 = same continent
     *                 - 255 = unrestricted
     * @param int $timeout timeout (in seconds) used for ping and fsockopen()
     */
    public function __construct(string $host = '', int $ttl = 255, int $timeout = 3)
    {
        $this->host = $host;
        $this->ttl = $ttl;
        $this->timeout = $timeout;
    }

    /**
     * @throws Exception
     */
    public function __invoke(string $host = '', int $ttl = 255, int $timeout = 3, string $method = 'fsockopen'): ?float
    {
        $this->host = $host;
        $this->ttl = $ttl;
        $this->timeout = $timeout;

        return $this->ping($method);
    }

    /**
     * Get the ttl.
     *
     * @return int the current ttl for Ping
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Set the ttl (in hops).
     *
     * @param int $ttl TTL in hops
     *
     * @return $this
     */
    public function setTtl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get the timeout.
     *
     * @return int current timeout for Ping
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set the timeout.
     *
     * @param int $timeout time to wait in seconds
     *
     * @return $this
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get the host.
     *
     * @return string
     *                The current hostname for Ping
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Set the host.
     *
     * @param string $host host name or IP address
     *
     * @return $this
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the port (only used for fsockopen method).
     *
     * @return int the port used by fsockopen pings
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Set the port (only used for fsockopen method).
     *
     * Since regular pings use ICMP and don't need to worry about the concept of
     * 'ports', this is only used for the fsockopen method, which pings servers by
     * checking port 80 (by default).
     *
     * @param int $port
     *                  Port to use for fsockopen ping (defaults to 80 if not set)
     *
     * @return $this
     */
    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Return the command output when method=exec.
     */
    public function getCommandOutput(): string
    {
        return $this->commandOutput;
    }

    /**
     * Matches an IP on command output and returns.
     *
     * @return string
     */
    public function getIpAddress(): ?string
    {
        $out = [];
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $this->commandOutput, $out)) {
            return $out[0];
        }

        return null;
    }

    /**
     * Ping a host.
     *
     * @param string $method
     *                       Method to use when pinging:
     *                       - exec (default): Pings through the system ping command. Fast and
     *                       robust, but a security risk if you pass through user-submitted data.
     *                       - fsockopen: Pings a server on port 80.
     *                       - socket: Creates a RAW network socket. Only usable in some
     *                       environments, as creating a SOCK_RAW socket requires root privileges.
     *
     * @throws InvalidArgumentException if $method is not supported
     * @throws Exception
     *
     * @return float Latency as float, in ms, if host is reachable or null if host is down
     */
    public function ping(string $method = 'exec'): ?float
    {
        if (empty($this->host)) {
            throw new Exception('Error: Host name not supplied.', -1);
        }

        switch ($method) {
            case 'exec':
                $latency = $this->pingExec();

                break;

            case 'fsockopen':
                $latency = $this->pingFsockopen();

                break;

            case 'socket':
                $latency = $this->pingSocket();

                break;

            default:
                throw new InvalidArgumentException('Unsupported ping method.', -2);
        }

        // Return the latency.
        return $latency;
    }

    /**
     * The exec method uses the possibly insecure exec() function, which passes
     * the input to the system. This is potentially VERY dangerous if you pass in
     * any user-submitted data. Be SURE you sanitize your inputs!
     *
     * @return float latency, in ms
     */
    private function pingExec(): ?float
    {
        $latency = null;

        $host = escapeshellcmd($this->host);

        // Exec string for Windows-based systems.
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            // -n = number of pings; -i = ttl; -w = timeout (in milliseconds).
            $exec_string = sprintf('chcp 437 && ping -n 1 -i %s -w %d %s', $this->ttl, $this->timeout * 1000, $host);
        } // Exec string for Darwin based systems (OS X).
        elseif ('DARWIN' === strtoupper(PHP_OS)) {
            // -n = numeric output; -c = number of pings; -m = ttl; -t = timeout.
            $exec_string = sprintf('ping -n -c 1 -m %d -t %d %s', $this->ttl, $this->timeout, $host);
        } // Exec string for other UNIX-based systems (Linux).
        else {
            // -n = numeric output; -c = number of pings; -t = ttl; -W = timeout
            $exec_string = sprintf('ping -n -c 1 -t %d -W %d %s 2>&1', $this->ttl, $this->timeout, $host);
        }

        exec($exec_string, $output, $return);

        // Strip empty lines and reorder the indexes from 0 (to make results more
        // uniform across OS versions).
        $this->commandOutput = implode('', $output);
        $output = array_values(array_filter($output));

        // If the result line in the output is not empty, parse it.
        if (!empty($output[1])) {
            // Search for a 'time' value in the result line.
            $response = preg_match('/time[<=](?<time>[.0-9]+)(?:|\\s)ms/', $output[1] . $output[2], $matches);

            // If there's a result and it's greater than 0, return the latency.
            if ($response > 0 && isset($matches['time'])) {
                /** @var float $latency */
                $latency = round((float) $matches['time'], 4);
            }
        }

        return $latency;
    }

    /**
     * The fsockopen method simply tries to reach the host on a port. This method
     * is often the fastest, but not necessarily the most reliable. Even if a host
     * doesn't respond, fsockopen may still make a connection.
     *
     * @return float Latency, in ms
     */
    private function pingFsockopen(): ?float
    {
        $start = microtime(true);
        // fsockopen prints a bunch of errors if a host is unreachable. Hide those
        // irrelevant errors and deal with the results instead.
        $fp = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$fp) {
            return null;
        }
        $latency = microtime(true) - $start;

        return round($latency * 1000, 4);
    }

    /**
     * The socket method uses raw network packet data to try sending an ICMP ping
     * packet to a server, then measures the response time. Using this method
     * requires the script to be run with root privileges, though, so this method
     * only works reliably on Windows systems and on Linux servers where the
     * script is not being run as a web user.
     *
     * @return float Latency, in ms
     */
    private function pingSocket(): ?float
    {
        // Create a package.
        $type = "\x08";
        $code = "\x00";
        $checksum = "\x00\x00";
        $identifier = "\x00\x00";
        $seq_number = "\x00\x00";
        $package = $type . $code . $checksum . $identifier . $seq_number . $this->data;

        // Calculate the checksum.
        $checksum = $this->calculateChecksum($package);

        // Finalize the package.
        $package = $type . $code . $checksum . $identifier . $seq_number . $this->data;

        // Create a socket, connect to server, then read socket and calculate.
        // Note: Sudo permission is required to execute PHPUnit tests.
        if ($socket = socket_create(AF_INET, SOCK_RAW, 1)) {
            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, [
                'sec' => $this->timeout,
                'usec' => 0,
            ]);
            // Prevent errors from being printed when host is unreachable.
            @socket_connect($socket, $this->host);
            $start = microtime(true);
            // Send the package.
            @socket_send($socket, $package, \strlen($package), 0);
            if (false !== @socket_read($socket, 255)) {
                $latency = microtime(true) - $start;
                $latency = round($latency * 1000, 4);
            } else {
                return null;
            }

            // Close the socket.
            socket_close($socket);

            return $latency;
        }

        return null;
    }

    /**
     * Calculate a checksum.
     *
     * @param string $data
     *                     Data for which checksum will be calculated
     *
     * @return string
     *                Binary string checksum of $data
     */
    private function calculateChecksum(string $data): string
    {
        if (\strlen($data) % 2) {
            $data .= "\x00";
        }

        /** @var int[] $bit */
        $bit = unpack('n*', $data);
        $sum = array_sum($bit);

        while ($sum >> 16) {
            $sum = ($sum >> 16) + ($sum & 0xFFFF);
        }

        return pack('n*', ~$sum);
    }
}
