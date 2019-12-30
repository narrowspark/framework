## Running Tests

The Narrowspark project uses a third-party services which automatically runs tests
for any submitted [patch][5]. If the new code breaks any test,
the pull request will show an error message with a link to the full error details.

In any case, it’s a good practice to run tests locally before submitting a
:doc:`patch <patches>` for inclusion, to check that you have not broken anything.

### Before Running the Tests

To run the Narrowspark test suite, install the external dependencies used during the
tests, such as Doctrine, Twig, and Monolog. To do so,
:doc:`install Composer </setup/composer>` and execute the following:

```bash
composer update
```

Running the Tests
-----------------

Then, run the test suite from the Narrowspark root directory with the following
command:

```bash
composer test
```

The output should display `OK`. If not, read the reported errors to figure out
what’s going on and if the tests are broken because of the new code.

> :tip: The entire Narrowspark suite can take up to several minutes to complete. If you
> want to test a single component
> - `phpunit`: type its path after the `phpunit` command,
> - `composer`: type the folder name in kebab case after `composer test:` command
>
> e.g.:

```bash
php vendor/bin/phpunit --testsuite="Narrowspark WebServer Component Test Suite"
```

```bash
composer test:web-server
```

You will find all testsuites in the `phpunit.xml.dist` file, the main `composer.json scripts` section or run `composer list test`.

> :tip: On Windows, install the [Cmder][1], [ConEmu][2], [ANSICON][3] or [Mintty][4] free applications to see colored test results.

[1]: http://cmder.net/
[2]: https://conemu.github.io/
[3]: https://github.com/adoxa/ansicon/releases
[4]: https://mintty.github.io/
