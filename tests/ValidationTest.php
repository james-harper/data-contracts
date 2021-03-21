<?php

namespace DataContracts\Tests;

use DataContracts\Tests\Constants\Group;
use DataContracts\DataContract;

uses()->group(Group::VALIDATION);

class HumanContract extends DataContract
{
    protected $schema = 'tests/data/person.json';
}

it('gets validation rules for the contract', function () {
    $rules = HumanContract::validationRules();
    $this->assertEquals([
        'name' => ['required'],
        'age' => ['min:0', 'numeric', 'required'],
    ], $rules);
});

it('removes the required rule', function () {
    $this->assertEquals([
       'name' => [],
       'age' => ['min:0', 'numeric'],
   ], HumanContract::validationRulesOptional());
});

it('removes the given rule', function () {
    $this->assertEquals([
        'name' => ['required'],
        'age' => ['min:0', 'required'],
    ], HumanContract::rulesExcept('numeric'));

    $this->assertEquals(
        HumanContract::validationRules(),
        HumanContract::rulesExcept('made-up-rule')
    );
});

class AdminContract extends DataContract
{
    protected $schema = 'tests/data/user.json';
}

it('removes an array of rules', function () {
    $this->assertEquals([
        'name' => ['min:3', 'required'],
        'email' => ['email', 'required'],
        'password' => ['required'],
    ], AdminContract::rulesExcept(['numeric', 'alpha_num']));
});

class BlogPostContract extends DataContract
{
    protected $schema = 'tests/data/post.json';
}

it('removes all instances of a rule with parameters', function () {
    $this->assertEquals([
        'title' => ['required'],
        'body' => ['required'],
    ], BlogPostContract::rulesExcept('min'));
});

it('removes all instances of a rule with parameters when part of an array', function () {
    $this->assertEquals([
        'title' => ['required'],
        'body' => ['required'],
    ], BlogPostContract::rulesExcept(['min', 'numeric']));
});
