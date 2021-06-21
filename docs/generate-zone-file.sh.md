# Zone File

A simple PHP class for generating DNS [zone files](https://en.wikipedia.org/wiki/Zone_file).


## Features

- Supports [A](#addastr-name-str-ip-int-ttl-method), [AAAA](#addaaaastr-name-str-ip-int-ttl-method), [CNAME](#addcnamestr-name-str-cname-int-ttl-method), [TXT](#addtxtstr-name-str-data-int-ttl-method), [MX](#addmxstr-name-int-pri-str-server-int-ttl-method), and [NS](#addnsstr-ns-int-ttl-method) records
- Compatible with:
	- [AWS Route 53](https://aws.amazon.com/route53/)
	- [DNS Made Easy](https://dnsmadeeasy.com/)
- [Shell script to deploy to Route 53](#push-to-route-53sh)
- [RFC 1035](https://tools.ietf.org/html/rfc1035)/[RFC 1034](https://tools.ietf.org/html/rfc1034) compliant-*ish*


## Requirements
- ZoneFile.php
	- [`php-cli`](https://www.php.net/manual/en/features.commandline.php)
- push-to-route-53.sh
	- [`awscli`](https://aws.amazon.com/cli/)
- generate-zone-file.sh
	- [`php-cli`](https://www.php.net/manual/en/features.commandline.php)
	- [`git`](https://git-scm.com/)
	- [`awscli`](https://aws.amazon.com/cli/) (if using the deploy to Route 53 feature)


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

The above code generates the output below:

```
$ORIGIN example.com.
$TTL 180
;example.com.
www		120		IN		A		93.184.216.34
www		120		IN		AAAA		2606:2800:220:1:248:1893:25c8:1946
```



## ZoneFile.php

#### ZoneFile(str `domain`[, int `ttl`]) Class
- `domain` - the domain the zone file is being generated for. This must be a fully qualified domain name that ends with a period (i.e. `example.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, that will be used for records where a TTL is not specified. The default value is `60`


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);

?>
```

---

#### addA(str `name`, str `ip`[, int `ttl`]) Method
- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `ip` - the IPv4 address
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);
$zone_file->addA('example.com.', '93.184.216.34', 120);
$zone_file->addA('www', '93.184.216.34', 180);
$zone_file->addA('www1.example.com.', '93.184.216.34');

?>
```

---

#### addAAAA(str `name`, str `ip`[, int `ttl`]) Method
- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `ip` - the IPv6 address
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);
$zone_file->addAAAA('example.com.', '2606:2800:220:1:248:1893:25c8:1946', 120);
$zone_file->addAAAA('www', '2606:2800:220:1:248:1893:25c8:1946', 180);
$zone_file->addAAAA('www1.example.com.', '2606:2800:220:1:248:1893:25c8:1946');

?>
```

---

#### addCname(str `name`, str `cname`[, int `ttl`]) Method
- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `cname` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);
$zone_file->addCname('www', 'www1', 180);
$zone_file->addCname('www2.example.com.', 'www3');
$zone_file->addCname('www4', 'www5.example.com.');

?>
```

---

#### addTxt(str `name`, str `data`[, int `ttl`]) Method
- `name` - the host name. This can be a relative host name (i.e. `www`) or a fully qualified domain name that ends with a period (i.e. `www.example.com.`)
- `data` - the data
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);
$zone_file->addTxt('example.com.', 'key=value', 120);
$zone_file->addTxt('www', 'key=value', 180);
$zone_file->addTxt('www1.example.com.', 'key=value');

?>
```

---


#### addMx(str `name`, int `pri`, str `server`[, int `ttl`]) Method
- `name` - the host name. This can be a relative host name (i.e. `mail`) or a fully qualified domain name that ends with a period (i.e. `example.com.`)
- `pri` - the MX record's priority
- `server` - the host name of the mail server. This can be a relative host name (i.e. `mail`) or a fully qualified domain name that ends with a period (i.e. `mail.example.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);
$zone_file->addMx('example.com.', 10, 'mail', 120);
$zone_file->addMx('example.com.', 10, 'mail1.example.com.');
$zone_file->addMx('example.com.', 20, 'mail2.example.com.');

?>
```

---


#### addNs(str `ns`[, int `ttl`]) Method
- `ns` - the host name of the name server. This must be a fully qualified domain name that ends with a period (i.e. `ns.nameserver.com.`)
- `ttl` (optional) - the time to live (TTL), in seconds, for the record. If not specified, the zone file's default `ttl` will be used


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 240);
$zone_file->addNs('example.com.', 'ns.nameserver.com.', 120);

?>
```

---


#### output() Method
Outputs the zone file


##### Example
```php
<?php

require('ZoneFile.php');

$zone_file = new ZoneFile('example.com.', 180);

$zone_file->addA('www', '93.184.216.34', 120);
$zone_file->addAAAA('www', '2606:2800:220:1:248:1893:25c8:1946', 120);

echo $zone_file->output();

?>
```

The above code generates the output below:

```
$ORIGIN example.com.
$TTL 180
;example.com.
www		120		IN		A		93.184.216.34
www		120		IN		AAAA		2606:2800:220:1:248:1893:25c8:1946
```



## push-to-route-53.sh

This shell script pushes a DNS zone file to AWS Route 53

### Example

```sh
#!/bin/sh

php zone-file-generator.php > ~/zone-file.txt
sh push-to-route-53.sh example.com ~/zone-file.txt
```



## generate-zone-file.sh

This shell script further automates the process of generating a DNS zone file, and optionally, pushing it to Route 53.

It requires that you have a GitHub repository, like [this](https://github.com/prodctrl/zone-file-example), with all of your DNS records in a file named `zone-file-generator.php`. The repo can be public or private (assuming you have access).

### Command-Line Parameters
- `domain` - the domain the zone file is being generated for. This must be a fully qualified domain name that ends with a period (i.e. `example.com.`)
- `github_repo` - the GitHub repository that contains your `zone-file-generator.php` file (i.e. `github-username/github-repo-name`)
- `argX` (optional) - you can pass up to 32 custom arguments to `zone-file-generator.php`. These will be accessible in PHP using the `$argv[]` array (indexes 3-34), and the values can be pretty much whatever you want. This feature is useful when the output of your `zone-file-generator.php` script is dynamic

### Example
#### Without Custom Arguments
```sh
#!/bin/sh

curl https://raw.githubusercontent.com/prodctrl/zone-file/master/generate-zone-file.sh > ~/generate-zone-file.sh
sh ~/generate-zone-file.sh example.com. github-username/github-repo-name
rm ~/generate-zone-file.sh
```

#### With Custom Arguments
```sh
#!/bin/sh

curl https://raw.githubusercontent.com/prodctrl/zone-file/master/generate-zone-file.sh > ~/generate-zone-file.sh
sh ~/generate-zone-file.sh example.com. github-username/github-repo-name www1 node5
rm ~/generate-zone-file.sh
```