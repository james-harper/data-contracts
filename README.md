# Data Contracts

Data contracts defined here can be shared between multiple services.

This was originally designed for use with Laravel projects in mind, so there
are some Laravel-specific convenience methods - dealing with validation rules or
automatically converting to API resources, for example.

`Todo` and `User` data contracts have been included as examples.
The following commands can be run to remove all traces of them.

```shell
php cli delete:contract Todo
php cli delete:contract User
```
## Installation

This can be included in other packages via composer.

```shell
composer require james-harper/data-contracts:^1.0
```

For any serious use, it is recommended to fork this repository and add data
contracts that are appropriate to the domain you are working on.
And then use the forked version via `composer`.

## Defining Contracts

A data contract is defined in a JSON file which follows the [JSON Schema](https://json-schema.org/) specification.
```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "title": "Person",
  "description": "A Person",
  "type": "object",
  "properties": {
    "id": {
      "type": "integer"
    },
    "name": {
      "type": "string"
    }
  },
  "required": [
    "id",
    "name"
  ]
}
```

The schema definition can be validated against the specification using the following command:
```shell
php cli validate:schema {name}
```

The id field of the resource (which will be `id` by default) will not be returned from the `describe()` method. If a resource is using something different as its identifier - for example `guid` - the contract's `protected static $id` property can be updated to reflect this.

```php
use DataContracts\DataContract;

class User extends DataContract
{
  protected $schema = 'path/to/file.json';
  protected static $id = 'guid';
}
```

PHP data contracts can be created by extending `DataContracts\DataContract` and then setting the `$schema` property as a link to the JSON definition. Schema paths should be relative to the project root. A `RuntimeException` will be thrown if no schema is defined.
```php
use DataContracts\DataContract;

class User extends DataContract
{
  protected $schema = 'path/to/file.json';
}
```

DataContracts can be generated using the following composer command:
```shell
./cli make:contract {Name}
```

## Usage

Each DataContract has a static `describe()` method which will return all of the fields on the contract (that are not considered `private`) and an `all()` method that will display everything.

There is also a `validationRules()` method that can be used to get any [validation rules](https://laravel.com/docs/8.x/validation#available-validation-rules) for the contract. These should use [Laravel validation rules]((https://laravel.com/docs/8.x/validation#available-validation-rules)) so that the output can be used directly for Request validation in Laravel projects. Transformers must be applied to get valid Laravel rules from a JSON Schema: these can be found in `src/Rules`.

```php
use DataContracts\User as UserContract;

UserContract::describe();
// ['first_name', 'last_name', 'email', 'role']
UserContract::all();
// ['first_name', 'last_name', 'email', 'role']
UserContract::validationRules();
// ['first_name' => ['required']...]
```

Some helpers exist to make working with validation rules easier.
`validationRulesOptional()` gets all rules but removes any `required` rules. This is useful for partial updates when not all fields would be expected. And `rulesExcept($rule)` can be used to filter out the chosen rule.
```php
UserContract::validationRulesOptional();
// ['account_id' => ['numeric']...]
UserContract::rulesExcept('numeric');
UserContract::rulesExcept(['numeric', 'min'])
```

DataContracts can be used anywhere, but should be defined in this repository for shareability purposes.

### Usage with Laravel Eloquent models
A DataContract can be used to filter Eloquent models down to only fields that are on the DataContract
```php
$user = new User([
    'id' => 1,
    'name' => 'James',
    'email' => 'james@example.com',
    'extraField' => 123,
]);
UserContract::fromEloquent($user);
// ['id' => 'James', 'name' => 'James', 'email' => 'james@example.com']
```

### Usage with Laravel API Resources
Simarly, DataContracts can be used to filter [Laravel API Resources](https://laravel.com/docs/8.x/eloquent-resources) down to only fields that are on the DataContract
```php
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    public function toArray()
    {
        return UserContract::fromResource($this);
    }
}
```

If any additional transformation needs to be performed, it is a case of simply overwriting the default transformation
```php
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {
    public function toArray()
    {
        $user = UserContract::fromResource($this);
        $user['name'] = strtoupper($this->name);
        return $user;
    }
}
```

## CLI Tool
A CLI tool has been created to simplify common tasks and encourage the use of consistent patterns. It has been designed in a similar way to Laravel's `artisan` so it should feel familiar to use.
It can be run using `./cli` or `php cli`. Running the tool without any additional arguments will show the help screen which lists all available commands.

The following commands can be used:
- Flush cache
    - `php cli flush:cache`
    - `php cli flush`
- Run linting
    - `php cli lint:run`
    - `php cli lint`
    - `php cli lint --fix`
- Generate console command
    - `php cli make:command <name>`
    - `php cli new <name>`
- Generate data contract
    - `php cli make:contract <name>`
    - `php cli schema <name>`
- Delete data contract
    - `php cli delete:contract <name>`
    - `php cli delete <name>`
    - `php cli remove <name>`
- Generate console formatting pattern
    - `php cli make:pattern <name> <delimiter>`
    - `php cli make:pattern <name> <opening-delimiter> <closing-delimiter>`
    - `php cli pattern <name> <delimiter>`
- Generate validation rule
    - `php cli make:rule <name>`
    - `php cli rule <name>`
- Run tests
    - `php cli test:run`
    - `php cli test`
    - Run group(s) of tests
    - `php cli test cache`
    - `php cli test rules,schemas`
    - `php cli test cache,contracts,rules,schemas,validation`
- Update CHANGELOG.md
    - `php cli update:changelog`
    - `php cli changelog`
    - `php cli make:log`
    - `php cli generate:log`
- Validate JSON Schema
    - `php cli validate:schema <name>`
    - `php cli validate <name>`
- Validate all JSON Schemas
    - `php cli validate:all`

For additional help with any command the following command can be run:
- `php cli -h <command>`
- For example:
    - `php cli -h make:command`
    - `php cli -h new`

CLI documentation is stored in markdown files in `console/docs`

Tab completion for the CLI can be enabled by running the following command:
```shell
source console/scripts/cli_autocompletion.sh
```

## Notes
- Since `DataContracts` do not change until there is an update to this package, they are a good candidate to be cached. There is an in-built caching support. By default `Illuminate\Cache` is used with the `file` driver, but any PSR-16 compliant cache can be set with `DataContract::setCache($cache)`
- It is recommended to alias `DataContracts` to have a `Contract` suffix to avoid them being confused with models. For example: `use DataContracts\User as UserContract`. A statement like `User::all()` would be valid for both an Eloquent model and a DataContract. `UserContract::all()` makes it much easier to tell at a glance.
- In order to avoid autoloading issues, any sub-directories within directories that are configured to use PSR-4 autoloading (`console/`, `src/`, `tests/`) should begin with an uppercase letter. Folders that are not intended to contain any `.php` files (such as `console/scripts`) do not need to follow this rule.
- `composer test` can be used to run the whole test suite or the CLI test command can be used `php cli test cache,contracts,rules,schemas,validation` to run just a specific group of tests.
- JSON Schema dates/times can be quite fiddly at times. Here are some examples of the formats that can be used. `"full-date full-time"` will match `"Y-m-d h:i:s"`. `"date-time"` expects a T between the date and time portions.
```
date-fullyear   = 4DIGIT
date-month      = 2DIGIT  ; 01-12
date-mday       = 2DIGIT  ; 01-28, 01-29, 01-30, 01-31 based on
                            ; month/year
time-hour       = 2DIGIT  ; 00-23
time-minute     = 2DIGIT  ; 00-59
time-second     = 2DIGIT  ; 00-58, 00-59, 00-60 based on leap second
                            ; rules
time-secfrac    = "." 1*DIGIT
time-numoffset  = ("+" / "-") time-hour ":" time-minute
time-offset     = "Z" / time-numoffset

partial-time    = time-hour ":" time-minute ":" time-second
                    [time-secfrac]
full-date       = date-fullyear "-" date-month "-" date-mday
full-time       = partial-time time-offset

date-time       = full-date "T" full-time
```
