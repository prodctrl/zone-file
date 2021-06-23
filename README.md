# Zone File

A simple PHP class for generating DNS [zone files](https://en.wikipedia.org/wiki/Zone_file).


## Features

- Supports [A](#addastr-name-str-ip-int-ttl-method), [AAAA](#addaaaastr-name-str-ip-int-ttl-method), [CNAME](#addcnamestr-name-str-cname-int-ttl-method), [TXT](#addtxtstr-name-str-data-int-ttl-method), [MX](#addmxstr-name-int-pri-str-server-int-ttl-method), and [NS](#addnsstr-ns-int-ttl-method) records
- Compatible with:
	- [AWS Route 53](https://aws.amazon.com/route53/)
	- [DNS Made Easy](https://dnsmadeeasy.com/)
- [Shell script to deploy to Route 53](#push-to-route-53sh)
- [RFC 1035](https://tools.ietf.org/html/rfc1035)/[RFC 1034](https://tools.ietf.org/html/rfc1034) compliant-*ish*


## Example

```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 180);

$zone_file->addA('www', '93.184.216.34', 120);
$zone_file->addAAAA('www', '2606:2800:220:1:248:1893:25c8:1946', 120);

echo $zone_file->output();

?>
```

The code above generates the output below:

```
$ORIGIN example.com.
$TTL 180
;example.com.
www		120		IN		A		93.184.216.34
www		120		IN		AAAA		2606:2800:220:1:248:1893:25c8:1946
```