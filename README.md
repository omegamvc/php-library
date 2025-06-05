<p align="center">
    <a href="https://omegamvc.github.io" target="_blank">
        <img src="https://github.com/omegamvc/omega-assets/blob/main/images/logo-omega.png" alt="Omega Logo">
    </a>
</p>

<p align="center">
    <a href="https://omegamvc.github.io">Documentation</a> |
    <a href="https://github.com/omegamvc/omegamvc.github.io/blob/main/README.md#changelog">Changelog</a> |
    <a href="https://github.com/omegamvc/omega/blob/main/CONTRIBUTING.md">Contributing</a> |
    <a href="https://github.com/omegamvc/omega/blob/main/CODE_OF_CONDUCT.md">Code Of Conduct</a> |
    <a href="https://github.com/omegamvc/omega/blob/main/LICENSE">License</a>
</p>

# PHP MVC

Php mvc with minimum mvc framework. is simple and easy to use

## Feature
- MVC base
- Container (dependency injection)
- Route
- Model (database class relation)
- View and Controller
- [MyQuery](#Built-in-Query-Builder) (database query builder)
- [Collection](#Collection) (array collection)
- [Console](#Console) (assembling beautiful console app)
- Template (create class using class generator)
- Cron
- Now (time managing)
- Http request and response
- [Str](#Str) (string manipulation)

## Collection
Array collection, handel functional array as chain method

### Create New Collection
```php
$coll = new Collection(['vb_net', 'c_sharp', 'java', 'python', 'php', 'javascript', 'html']);

$arr = $coll
  ->remove('html')
  ->sort()
  ->filter(fn ($item) => strlen($item) > 4)
  ->map(fn ($item) => ucfirst($item))
  ->each(function($item) {
    echo $item . PHP_EOL;
  })
  ->all()
;

// arr = ['c_sharp', 'javascript', 'python', 'vb_net']
```
[🔝 Back to contents](#Feature)

### Available Methods
- `add()`
- `remove()`
- `set()`
- `clear()`
- `replace()`
- `each()`
- `map`
- `filter()`
- `sort()`
- `sortDesc()`
- `sortKey()`
- `sortKeyDesc()`
- `sortBy()`
- `sortByDesc()`
- `all()`

[🔝 Back to contents](#Feature)

## Console

Assembling beautifully console app make easy

- naming parameter
- coloring console (text and background)

### Build simple console app
```php
class GreatConsole extends Console
{
  public function main()
  {
    // getter to get param form cli argument
    $name = $this->name ?? 'animus';

    style("Great console Application")
    	->textGreen()
        ->newLines()
        ->push("hay my name is ")
        ->push($name)
        ->textYellow()
        ->out()
    ;
  }
}
```

**Run your app**

- create bootstrapper
```php
#!usr/bin/env php

// $argv come with default global php
return (new greatConsole($argv))->main();

```

- on your console
```bash
php cli create --name php_mvc

# output:
# Great console application
# hay my name is php_mvc
```
[🔝 Back to contents](#Feature)

## Str

Make string manipulation.

```php
Str::chartAt('i love php', 3); // o
Str::concat(['i', 'love', 'php']); // i love php
Str::indexOf('i love php', 'p'); // 8
Str::lastIndexOf('i love php', 'p'); // 10
Str::match('i love php', '/love/'); // love
// ...
// and many more
```
- `chartAt`
- `concat`
- `indexOf`
- `lastIndexOf`
- `match`
- `slice`
- `split`
- `replace`
- `toUpperCase`
- `toLowerCase`
- `firstUpper`
- `firstUpperAll`
- `toSnackCase`
- `toKebabCase`
- `toPascalCase`
- `toCamelCase`
- `contains`
- `startsWith`
- `endsWith`
- `slug`
- `template`
- `length`
- `repeat`
- `isString`
- `isEmpty`
- `fill`
- `fillEnd`
- `limit`

### Custom macro

custom macro string;

```php
Str::macro('prefix', fn($text, $prefix) => $prefix.$test);

echo Str::prefix('cool', 'its '); // its cool
```

### String class

use chain string class.

```php
$string = new Text('I Love rust');

echo $string->replace('rust', 'php')->lower()->slug();
// i-love-php

echo $string->length(); // 10
echo $string->isEmpty(); // false
```

### String Regex

```php
Str::is('some@email.com', Regex::EMAIL); // true
```

available regex
- `email`
- `user`
- `plain_text`
- `slug`
- `html_tag`
- `js_inline`
- `password_complex`
- `password_moderate`
- `date_yyyymmdd`
- `date_ddmmyyyy`
- `date_ddmmmyyyy`
- `ip4`
- `ip6`
- `ip4_6`
- `url`

[🔝 Back to contents](#Feature)

## Documentation

<table>
  <thead style="background-color:black;color:white">
    <tr>
      <th>Package Name</th>
      <th>Description</th>
      <th>Read</th>
    </tr>
  </thead>
  <tbody>
     <tr>
        <td><code>Database</code></td>
        <td>Provides a database abstraction, CRUD operations, and a QueryBuilder.</td>
        <td><a href="https://github.com/omegamvc/php-library/blob/main/docs/Database.md" target="_blank">📖</a></td>
      </tr>
    <!--tr>
      <td><code>Serializable Closure</code></td>
      <td>Enables the serialization of closures in a secure and portable way.</td>
      <td><a href="https://example.com/serializable-closure" target="_blank">📖</a></td>
    </tr-->
    <tr>
      <td><code>Validator</code></td>
      <td>Provides a flexible and extensible validation system for input data.</td>
      <td><a href="https://github.com/omegamvc/php-library/blob/main/docs/Validator.md" target="_blank">📖</a></td>
    </tr>
  </tbody>
</table>


## License

This project is open-source software licensed under the [GNU General Public License v3.0](LICENSE).