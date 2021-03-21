<?php

namespace DataContracts\Tests;

use DataContracts\Todo as TodoContract;
use DataContracts\User as UserContract;
use DataContracts\Tests\Constants\Group;

uses()->group(Group::SCHEMAS);

it('can create a valid users contract', function () {
    $this->assertEquals(UserContract::describe(), [
        'first_name',
        'last_name',
        'email',
        'role',
    ]);
});

it('can create a valid todos contract', function () {
    $this->assertEquals(TodoContract::describe(), [
        'description',
        'date',
        'completed',
    ]);
});
