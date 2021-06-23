# generate-zone-file.sh

This shell script further automates the process of generating a DNS zone file, and optionally, pushing it to Route 53.

It requires that you have a GitHub repository, like [this](https://github.com/prodctrl/zone-file-example), with all of your DNS records in a file named `zone-file-generator.php`. The repo can be public or private (assuming you have access).

## Requirements
- [`php-cli`](https://www.php.net/manual/en/features.commandline.php)
- [`git`](https://git-scm.com/)
- [`awscli`](https://aws.amazon.com/cli/) (if using the deploy to Route 53 feature)

## Command-Line Parameters
- `domain` - the domain the zone file is being generated for. This must be a fully qualified domain name that ends with a period (i.e. `example.com.`)
- `github_repo` - the GitHub repository that contains your `zone-file-generator.php` file (i.e. `github-username/github-repo-name`)
- `argX` (optional) - you can pass up to 32 custom arguments to `zone-file-generator.php`. These will be accessible in PHP using the `$argv[]` array (indexes 3-34), and the values can be pretty much whatever you want. This feature is useful when the output of your `zone-file-generator.php` script is dynamic

## Example
### Without Custom Arguments
```sh
#!/bin/sh

curl https://raw.githubusercontent.com/prodctrl/zone-file/master/generate-zone-file.sh > ~/generate-zone-file.sh
sh ~/generate-zone-file.sh example.com. github-username/github-repo-name
rm ~/generate-zone-file.sh
```

### With Custom Arguments
```sh
#!/bin/sh

curl https://raw.githubusercontent.com/prodctrl/zone-file/master/generate-zone-file.sh > ~/generate-zone-file.sh
sh ~/generate-zone-file.sh example.com. github-username/github-repo-name www1 node5
rm ~/generate-zone-file.sh
```