This command generates a new validation rule.

Validation rules are intended to map JSON schema properties against
the rules used by Laravel's validation. So any newly created
rules should directly match an existing Laravel rule:
`https://laravel.com/docs/8.x/validation#available-validation-rules`

A PHP rule file will be created in `src/rules`. This is where the logic
for transforming a JSON schema property to match a Laravel validation
rule will go.
This file will be automatically added to `src/Rules/RulesRegistry.php`
And a constant matching the Laravel rule name will also be added
to `src/Rules/LaravelRule.php`

When creating a rule it is advised to add some tests to `tests/RulesTest.php`
so that we can be sure that the rule is being applied as intended and
is not interfering with any existing rules in unexpected ways.

Some JSON Schema rules can have unusual edge cases or may not be
a direct one-to-one mapping so please be thorough when testing.
