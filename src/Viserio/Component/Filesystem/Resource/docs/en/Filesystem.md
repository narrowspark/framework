## Usage
The [Filesystem][0] class is the unique endpoint for local filesystem operations:

### Watch
```php
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;

$fs = new Filesystem();

$fs->watch('/path/to/some/directory', static function ($file, $event) {
    if ($event === FileChangeEvent::FILE_CREATED) {
        echo $file.' was created!';
    }
});
```

If you want to bail out of the watch events, the callback can return false to stop the process.
```php
$fs = new Filesystem();

$fs->watch('/path/to/some/directory', static function ($file, $event) {
    if ($event === FileChangeEvent::FILE_DELETED) {
        echo $file.' was deleted!';
        return false; // <- Will stop the watch process and continue with execution of the the rest of the application
    }
});
```

[0]: https://github.com/narrowspark/framework/blob/{version}/src/Viserio/Component/Filesystem/Filesystem.php
