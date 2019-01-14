## Coding Standards

To make every piece of code look and feel familiar, Narrowspark defines some coding standards that all contributions must follow.

These Narrowspark coding standards are based on the [PSR-1][1], [PSR-2][2], and [PSR-4][3] standards, followed by [PSR-5][4], [PSR-12][5] and [PSR-19][6] that are on draft right now, so you may already know most of them.

### Making your Code Follow the Coding Standards

Instead of reviewing your code manually, Narrowspark makes it simple to ensure that your contributed code matches the expected code syntax.
Run this command to fix any problem:
```bash
composer cs
```

Fore more informations see Narrowspark [coding-standard][7].

If you forget to run this command and make a pull request with any syntax issue, our automated tools will warn you about that and will provide the solution.

### Documentation
* Add PHPDoc blocks for all classes, methods, and functions.
* Group annotations together so that annotations of the same type immediately follow each other, and annotations of a different type are separated by a single blank line.
* The `@package` and `@subpackage` annotations are not used.

### License
Narrowspark is released under the MIT license, and the license block has to be present at the top of every PHP file, before the namespace.

[1]: https://www.php-fig.org/psr/psr-1/
[2]: https://www.php-fig.org/psr/psr-2/
[3]: https://www.php-fig.org/psr/psr-4/
[4]: https://www.php-fig.org/psr/psr-5/
[5]: https://www.php-fig.org/psr/psr-12/
[6]: https://www.php-fig.org/psr/psr-19/
[7]: https://github.com/narrowspark/coding-standard
