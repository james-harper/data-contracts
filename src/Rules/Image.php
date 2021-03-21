<?php

namespace DataContracts\Rules;

use DataContracts\Schema\Constants\Option;
use DataContracts\Schema\Constants\Type;

/**
 * Image validation rule
 * @see https://laravel.com/docs/8.x/validation#rule-image
 */
class Image extends Rule
{
    /**
     * @var array $imageTypes Media types that we will accept as images
     */
    protected array $imageTypes = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/bmp',
        'image/gif',
        'image/svg',
        'image/svg+xml',
        'image/webp',
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

        $encoding = $this->property->getOption(Option::CONTENT_ENCODING);
        $mediaType = $this->property->getOption(Option::CONTENT_MEDIA_TYPE);

        return $encoding === Option::BASE_64 && in_array($mediaType, $this->imageTypes);
    }

    /**
     * Get the name that Laravel uses for this rule
     *
     * @return string
     */
    public function getLaravelName() : string
    {
        return LaravelRule::IMAGE;
    }
}
