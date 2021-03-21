<?php

namespace DataContracts\Console\Utilities\Formatting;

class Emoji
{
    const ALIEN = '👽';
    const AMBULANCE = '🚑';
    const ART = '🎨';
    const BACK = '🔙';
    const BIN = '🗑';
    const BOOKMARK = '🔖';
    const BOOKS = '📖';
    const BOOM = '💥';
    const BULB = '💡';
    const BUG = '🐛';
    const CHART = '📈';
    const CLOWN = '🤡';
    const CODE = '📝';
    const COFFIN = '⚰️';
    const CONSTRUCTION = '🚧';
    const CONSTRUCTION_WORKER = '👷';
    const EGG = '🥚';
    const GEM = '💎';
    const HAMMER = '🔨';
    const LIPSTICK = '💄';
    const LOCK = '🔒';
    const MAGNIFY = '🔍';
    const PACKAGE = '📦';
    const PAGE = '📄';
    const POOP = '💩';
    const PUSHPIN = '📌';
    const RECYCLE = '♻️';
    const ROCKET = '🚀';
    const ROTATING_LIGHT = '🚨';
    const SILHOUETTES = '👥';
    const SOUND = '🔊';
    const SPARKLES = '✨';
    const SPEECH = '💬';
    const TADA = '🎉';
    const THUMBS_UP = '👍';
    const THUMBS_DOWN = '👎';
    const TOILET = '🚽';
    const TRUCK = '🚚';
    const WAVE = '👋';
    const WHITE_CHECK_MARK = '✅';
    const WRENCH = '🔧';

    protected static array $codes = [
        ':alien:' => self::ALIEN,
        ':ambulance:' => self::AMBULANCE,
        ':art:' => self::ART,
        ':back:' => self::BACK,
        ':bookmark:' => self::BOOKMARK,
        ':books:' => self::BOOKS,
        ':boom:' => self::BOOM,
        ':bug:' => self::BUG,
        ':bulb:' => self::BULB,
        ':busts_silhouette:' => self::SILHOUETTES,
        ':chart:' => self::CHART,
        ':code:' => self::CODE,
        ':coffin:' => self::COFFIN,
        ':clown:' => self::CLOWN,
        ':construction:' => self::CONSTRUCTION,
        ':construction_worker:' => self::CONSTRUCTION_WORKER,
        ':egg:' => self::EGG,
        ':gem:' => self::GEM,
        ':hammer:' => self::HAMMER,
        ':lipstick:' => self::LIPSTICK,
        ':lock:' => self::LOCK,
        ':loud_sound:' => self::SOUND,
        ':mag:' => self::MAGNIFY,
        ':package:' => self::PACKAGE,
        ':page_up:' => self::PAGE,
        ':poop:' => self::POOP,
        ':pushpin:' => self::PUSHPIN,
        ':recycle:' => self::RECYCLE,
        ':rocket:' => self::ROCKET,
        ':rotating_light:' => self::ROTATING_LIGHT,
        ':sparkles:' => self::SPARKLES,
        ':speech_balloon:' => self::SPEECH,
        ':tada:' => self::TADA,
        ':toilet:' => self::TOILET,
        ':truck' => self::TRUCK,
        ':wastebasket:' => self::BIN,
        ':wave:' => self::WAVE,
        ':white_checkmark:' => self::WHITE_CHECK_MARK,
        ':wrench:' => self::WRENCH,
        ':-1:' =>  self::THUMBS_DOWN,
        ':+1:' =>  self::THUMBS_UP,
    ];

    /**
     * Get an emoji for its code
     *
     * @param string $code
     * @return string
     */
    public static function get(string $code) : string
    {
        if (isset(Emoji::$codes[$code])) {
            return Emoji::$codes[$code];
        }

        return $code;
    }

    /**
     * Replace any emoji codes in a string with the actual emoji
     *
     * @param string $text
     * @return string
     */
    public static function insertEmojis(string $text) : string
    {
        $emojiRegex = '/:([A-Za-z_0-9\+\-$]+):/';
        preg_match_all($emojiRegex, $text, $matches);

        foreach ($matches[1] as $match) {
            $text = str_replace(":$match:", Emoji::get(":$match:"), $text);
        }

        return $text;
    }
}
