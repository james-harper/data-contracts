<?php

namespace DataContracts\Tests;

use DataContracts\DataContract;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Http\Resources\Json\JsonResource;
use DataContracts\Tests\Constants\Group;

uses()->group(Group::CONTRACTS);

class PersonContract extends DataContract
{
    protected $schema = 'tests/data/person.json';
}

it('describes the fields on a contract', function () {
    $fields = PersonContract::describe();
    $this->assertEquals(['name', 'age'], $fields);
});

it('includes private keys when using all method', function () {
    $fields = PersonContract::all();
    $this->assertEquals(['id', 'name', 'age'], $fields);
});

class InvalidContract extends DataContract
{
}

it('throws an exception when no schema is defined', function () {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(InvalidContract::class . ': No schema defined');

    InvalidContract::describe();
});

class User extends Eloquent
{
    protected $guarded = [];
}

class UserContract extends DataContract
{
    protected $schema = 'tests/data/user.json';
}

it('can compare against an Eloquent model', function () {
    $eloquentUser = new User([
        'id' => 1,
        'name' => 'James',
        'email' => 'james@example.com',
        'extraField' => 123,
    ]);

    $this->assertEquals(
        [
            'id' => 1,
            'name' => 'James',
            'email' => 'james@example.com',
        ],
        UserContract::fromEloquent($eloquentUser)
    );
});

class UserResource extends JsonResource
{
}

it('compares Laravel API resources against contracts', function () {
    $eloquentUser = new User([
        'id' => 1,
        'name' => 'James',
        'email' => 'james@example.com',
        'password'=> 'secret',
        'birthday' => '1989-12-09',
    ]);

    $userResource = new UserResource($eloquentUser);
    $this->assertEquals([
        'id' => 1,
        'name' => 'James',
        'email' => 'james@example.com',
        'password'=> 'secret',
    ], UserContract::fromResource($userResource));
});

class TestContract extends DataContract
{
    protected $schema = 'tests/data/non-standard-id-field.json';
    protected static $id = 'user_id';
}

it('handles non-standard ID fields correctly', function () {
    $this->assertEquals(['name'], TestContract::describe());
    $this->assertEquals(['user_id', 'name'], TestContract::all());
});
