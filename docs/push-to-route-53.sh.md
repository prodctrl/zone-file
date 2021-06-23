## push-to-route-53.sh

This shell script pushes a DNS zone file to AWS Route 53

## Requirements
- [`awscli`](https://aws.amazon.com/cli/)

### Example

```sh
#!/bin/sh

php zone-file-generator.php > ~/zone-file.txt
sh push-to-route-53.sh example.com ~/zone-file.txt
```