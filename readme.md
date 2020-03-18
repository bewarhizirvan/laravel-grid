# LaravelGrid

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

```bash
$ composer require bewarhizirvan/laravel-grid
```

## Usage

For initiating new grid
```php
$grid = new \BewarHizirvan\LaravelGrid\LaravelGrid($parameters);
```
$parameters must be an array and is optional, all keys are optional
>checkClass  : Authorization Class must have can() function  
>paginate	: Rows per Page (default: 50)  
>provider	: Model  
>dir    : Grid direction { right, left (default) }  
>label	: Label at the top of Grid  
>label_extra	: Extra Info below Label  
>counterString	: Counter String ( default: Showing records %s — %s of %s )  
>headerCounter  : Enable/Disable Header Counter ( default: true)  
>footerCounter  : Enable/Disable Footer Counter ( default: true)  
>

### Functions
```php
$grid->setProvider($provider);  
$grid->setLabelButton($label = 'New', $route = '');
$grid->orderBy($col,$dir);
$grid->setTotal($count);
$grid->setIPsortable($name);
$grid->addColumn($name = 'id', $label = 'id',$filter = false, $sortable = false, $ValueCalculator = null, $ValueFormatter = null);
$grid->addFilter($name = 'id', $label = 'id', $operator = 'like', $options = null);
$grid->addFilterSelect($name = 'id', $label = 'id', $options = []);
$grid->addActionColumn($col = 'id', $active = false, $inverse = false);
$grid->addActionButton($type = 'default', $title='', $route = '/', $conditions = [], $colid = null);
```

### When finished do bellow
```php
$grid = $grid->render();
```
>the above step will generate an html code

### Static Function
```php
\BewarHizirvan\LaravelGrid\LaravelGrid::addContextMenu($value = '', $title = ['name'=>'','value'=>''], $rows = [], $right=false);
```

### Constants
```php
\BewarHizirvan\LaravelGrid\LaravelGrid::PARENT;
\BewarHizirvan\LaravelGrid\LaravelGrid::SUBMENU;
\BewarHizirvan\LaravelGrid\LaravelGrid::OK;
\BewarHizirvan\LaravelGrid\LaravelGrid::NOTOK;
\BewarHizirvan\LaravelGrid\LaravelGrid::ENABLED;
\BewarHizirvan\LaravelGrid\LaravelGrid::DISABLED;
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Credits

- [Bewar Hizirvan][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/bewarhizirvan/laravel-grid.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/bewarhizirvan/laravel-grid.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/bewarhizirvan/laravel-grid/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/bewarhizirvan/laravel-grid
[link-downloads]: https://packagist.org/packages/bewarhizirvan/laravel-grid
[link-travis]: https://travis-ci.org/bewarhizirvan/laravel-grid
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/bewarhizirvan
[link-contributors]: ../../contributors
