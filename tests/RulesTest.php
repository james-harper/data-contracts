<?php

namespace DataContracts\Tests;

use DataContracts\DataContract;
use DataContracts\Tests\Constants\Group;

uses()->group(Group::RULES);

class RulesContract extends DataContract
{
    protected $schema = 'tests/data/rules.json';
}

beforeEach(function () {
    $this->rules = RulesContract::validationRules();
});

/**
 * Check if the specified rule is within the array of rules
 *
 * @param string $rule
 * @param array $rulesArray
 * @return boolean
 */
function ruleInArray(string $rule, array $rulesArray) : bool
{
    return in_array($rule, $rulesArray);
};

it('applies the date rule to dates', function () {
    $this->assertEquals(['date'], $this->rules['date']);
    $this->assertEquals(['date'], $this->rules['dateTime']);
});

it('applies the email rule to emails', function () {
    $this->assertEquals(['email', 'required'], $this->rules['email']);
});

it('applies the in rule to enums', function () {
    $this->assertEquals(['in:one,two,three'], $this->rules['enum']);
    $this->assertEquals(['in:1,2,3,four,five'], $this->rules['mixedEnum']);
});

it('applies the not_in rule to enums with not modifier', function () {
    $this->assertEquals(['not_in:1,2,3'], $this->rules['notIn']);
    $this->assertEquals(['not_in:un,deux,trois'], $this->rules['notArray']);
});

it('applies the max rule', function () {
    $this->assertEquals(['max:100', 'numeric'], $this->rules['max']);
    $this->assertEquals(['max:100'], $this->rules['maxString']);
    $this->assertEquals(['array', 'max:3'], $this->rules['maxArray']);
});

it('applies the min rule', function () {
    $this->assertEquals(['min:100', 'numeric'], $this->rules['min']);
    $this->assertEquals(['min:100'], $this->rules['minString']);
    $this->assertEquals(['array', 'min:2'], $this->rules['minArray']);
});

it('applies the size rule', function () {
    $this->assertEquals(['numeric', 'size:42'], $this->rules['fortyTwo']);
    $this->assertEquals(['size:10'], $this->rules['tenChar']);
    $this->assertEquals(['array', 'size:7'], $this->rules['sevenElements']);
});

it('applies the between rule correctly', function () {
    // Using contains because we don't really care about any numeric/min/max rules
    // that we also expect to be generated
    $this->assertContains('between:5,10', $this->rules['between']);
    $this->assertContains('between:3,8', $this->rules['betweenString']);
    $this->assertContains('between:1,5', $this->rules['betweenArray']);
});

it('applies the regex rule', function () {
    $this->assertEquals(['regex:^[A-C][0-9]{3}$'], $this->rules['regex']);
    $this->assertEquals(['alpha'], $this->rules['regexAlpha']);
    $this->assertEquals(['alpha_dash'], $this->rules['regexAlphaDash']);
    $this->assertEquals(['alpha_num'], $this->rules['regexAlphaNum']);
    $this->assertEquals(['starts_with:word'], $this->rules['startsWith']);
    $this->assertEquals(['starts_with:one,two'], $this->rules['startsWithMultiple']);
});

it('applies the not_regex rule when the not modifier is used on regex', function () {
    $this->assertEquals(['not_regex:^[0-9]*$'], $this->rules['notRegex']);
});

it('applies the ip address rule', function () {
    $this->assertEquals(['ip'], $this->rules['ipv4']);
    $this->assertEquals(['ip'], $this->rules['ipv6']);
});

it('applies the multiple_of rule', function () {
    $this->assertContains('multiple_of:5', $this->rules['multipleOf']);
});

it('applies the date_format rule', function () {
    $this->assertEquals(['date_format:Y-m-d h:i:s'], $this->rules['dateFormat']);
    $this->assertEquals(['date_format:Y-m-d'], $this->rules['fullDate']);
    $this->assertEquals(['date_format:h:i:s'], $this->rules['fullTime']);
});

it('applies the uri rule', function () {
    $this->assertEquals(['url'], $this->rules['url']);
});

it('applies the boolean rule', function () {
    $this->assertEquals(['boolean'], $this->rules['boolean']);
});

it('applies the array rule', function () {
    $this->assertEquals(['array'], $this->rules['array']);
});

it('applies the distinct rule', function () {
    $this->assertEquals(['array', 'distinct'], $this->rules['distinct']);
});

it('applies the image rule', function () {
    $this->assertEquals(['image'], $this->rules['image']);
});

it('applies the all_of modifier correctly', function () {
    $this->assertEquals(['min:3', 'in:one,two,three', 'max:100'], $this->rules['allOf']);
});
