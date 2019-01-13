# Logger sender
Send logs files content to logs collector

## Installation

```bash
composer require azurre/php-logger-sender:"^1.0"
```

## Usage 

```php
$loader = include __DIR__ . '/vendor/autoload.php';
$sender = new \Azurre\Component\Logger\Sender(include __DIR__ . '/config.php');
try {
    $sender->run();
} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}";
}
```

### Config example

```php
<?php
return [
    'api' => 'https://logger.site.com/api/',
    'token' => '41689fe08fg67507e857d5248dc89388',
    'storages' => [
        __DIR__ . '/ram_drive/logs/',
        __DIR__ . '/logs/'
    ],
    'max_iterations' => 10,
];
```
