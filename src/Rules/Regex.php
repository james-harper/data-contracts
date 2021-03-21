<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Modifier;
use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * Regex validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-regex
 */
class Regex extends Rule
{
    /**
     * Type to pattern mapping
     *
     * @var array
     */
    protected $options = [
        Type::STRING => Option::PATTERN,
    ];

    /**
     * Special regex patterns that should produce named rules
     *
     * @return boolean
     */
    protected $specialPatterns = [
        /** @see https://github.com/laravel/framework/blob/2584678185d35e631f9a7e33241144e5540e0887/src/Illuminate/Validation/Concerns/ValidatesAttributes.php#L250 */
        LaravelRule::ALPHA => '^[\pL\pM]+$',
        /** @see https://github.com/laravel/framework/blob/2584678185d35e631f9a7e33241144e5540e0887/src/Illuminate/Validation/Concerns/ValidatesAttributes.php#L262 */
        LaravelRule::ALPHA_DASH => '^[\pL\pM\pN_-]+$',
        /** @see https://github.com/laravel/framework/blob/2584678185d35e631f9a7e33241144e5540e0887/src/Illuminate/Validation/Concerns/ValidatesAttributes.php#L278 */
        LaravelRule::ALPHA_NUM => '^[\pL\pM\pN]+$',
    ];

    protected $placeholderPatterns = [
        LaravelRule::STARTS_WITH => ['/^[A-Za-z]+/', '^', '.*$'],
    ];

    /**
     * Checks if the rule should be applied
     *
     * @return boolean
     */
    public function check() : bool
    {
        if (!$this->property->isOfType(Type::STRING)) {
            return false;
        }

        $modifier = $this->property->getOption(Option::MODIFIER);
        if ($modifier && $modifier === Modifier::NOT) {
            return false;
        }

        return $this->property->hasOption(Option::PATTERN);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        $pattern = $this->property->getOption(Option::PATTERN);

        if (in_array($pattern, $this->specialPatterns)) {
            return array_flip($this->specialPatterns)[$pattern];
        }

        foreach ($this->placeholderPatterns as $rule => [$regex, $before, $after]) {
            $placeholder = str_replace($before, '', $pattern);
            $placeholder = str_replace($after, '', $placeholder);
            if (preg_match($regex, $placeholder)) {
                // This may need replacing with something a bit more sophisticated
                // as it cannot current handle regular expressions containing |
                // It is basically swapping a regex OR (|), for a Laravel rule
                // delimiter (,)
                $placeholder = str_replace('|', ',', $placeholder);
                return $this->ruleWithValue($rule, $placeholder);
            }
        }

        return $this->ruleWithValue(LaravelRule::REGEX);
    }
}
