<img src="https://raw.githubusercontent.com/geerlingguy/Ping/1.x/Resources/Ping-Logo.png" alt="Ping for PHP Logo" />

# Ping

[![Build Status](https://travis-ci.org/geerlingguy/Ping.svg?branch=1.x)](https://travis-ci.org/geerlingguy/Ping)

A PHP class to ping hosts.

There are a ton of different methods of pinging a server using PHP, and I've found most to be poorly documented or downright dangerous in their implementation.

Therefore, I've created this simple class, which incorporates the three most popular ping methods (`exec()` with the system's `ping` utility, `fsockopen()`, and `socket_create()`). Each method has its benefits and drawbacks, and may work better or worse on a particular system.

## Usage

This is a very simple class. Just create an instance, and run `ping()`.

```php
$host = 'www.example.com';
$ttl = 128;
$timeout = 5;
$ping = new Ping($host, $ttl, $timeout);
$latency = $ping->ping();
if ($latency !== false) {
  print 'Latency is ' . $latency . ' ms';
}
else {
  print 'Host could not be reached.';
}
```

...or using the `setHost()`, `setTtl()`, `setTimeout()` or `setPort()` methods:

```php
$ping = new Ping();
$ping->setHost($host)
     ->setTtl(128)
     ->setTimeout(5)
     ->setPort(80)
     ->ping();
```

You can also use it as a function call:

```php
$ping = new Ping();
...
$ping('www.anotherexample.com');
$ping('example2.com');
```

## License

Ping is licensed under the MIT (Expat) license. See included LICENSE.md.
