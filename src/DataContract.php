<?php

namespace DataContracts;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use DataContracts\Cache\Constants as Cache;
use DataContracts\Cache\FileCache;
use DataContracts\Cache\SimpleCache;
use DataContracts\Rules\RulesRegistry;
use DataContracts\Schema\Constants\Modifier;
use DataContracts\Schema\Property;

abstract class DataContract
{
    /** @var string $id The identifier for this resource */
    protected static $id = 'id';
    /** @var string $schema Path to Json schema */
    protected $schema;
    /** @var Collection $properties */
    protected $properties;
    /** @var Collection $required */
    protected $required;

    /**
     * This is a cache shared by all DataContract instances.
     * The reason for this existing is that the data from DataContracts does
     * not change, but can sometimes be somewhat expensive to calculate.
     * (The schema has to be read and parsed, etc)
     *
     * Any PSR-16 compliant SimpleCache implementation can be set and used
     * here. A file-based Illuminate\Cache is being used as the default.
     * @var CacheInterface $cache
     */
    private static CacheInterface $cache;

    /**
     * A secondary cache that is being used to ensure that all DataContracts
     * are singletons and allows the same instances to be reused.
     *
     * The reason for not re-using the other cache is that there is no benefit
     * to allowing different cache implementations here. It is simple in purpose
     * and scope and is only used by this class internally.
     * @var SimpleCache $contractCache
     */
    private static SimpleCache $contractCache;

    /**
     * Private constructor because instantiation is handled internally
     * for performance and convenience reasons
     *
     * @throws RuntimeException
     */
    private function __construct()
    {
        if (!$this->schema) {
            throw new RuntimeException(static::class . ': No schema defined');
        }

        try {
            // This logic is so that the path works correctly
            // both as a standalone package and as a composer dependency
            $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
            $content = file_get_contents($path .'/'. $this->schema);
            $json = json_decode($content, true);
            $this->properties = new Collection($json['properties'] ?: []);
            $this->required = new Collection($json['required'] ?: []);
        } catch (\Exception $e) {
            throw new RuntimeException('Problem parsing JSON Schema');
        }
    }

    /**
     * Set the cache instance
     *
     * @param CacheInterface $cache
     * @return void
     */
    public static function setCache(CacheInterface $cache)
    {
        DataContract::$cache = $cache;
    }

    /**
     * Get the cache instance
     *
     * @return CacheInterface
     */
    private static function getCache()
    {
        // If it hasn't been set, use FileCache as default
        if (!isset(DataContract::$cache)) {
            DataContract::$cache = FileCache::make();
        }

        return DataContract::$cache;
    }

    /**
     * The outside world only ever interacts with DataContracts through static
     * method calls. This is for convenience (and a Laravel-esque) API.
     *
     * This method ensures that we are always using the same instance of each
     * contract type internally, which is useful because we must read and parse
     * a Json file every time one gets instantiated.
     *
     * @return static
     */
    protected static function getInstance() : DataContract
    {
        if (!isset(DataContract::$contractCache)) {
            DataContract::$contractCache = new SimpleCache();
        }

        if (!DataContract::$contractCache->has(static::class)) {
            DataContract::$contractCache->set(static::class, new static());
        }

        return DataContract::$contractCache->get(static::class);
    }

    /**
     * Show (non-private) fields on this contract
     *
     * @return array
     */
    public static function describe() : array
    {
        // array_values() to reset keys after filtering
        return array_values(
            array_filter(static::all(), function ($key) {
                return $key !== static::$id;
            })
        );
    }

    /**
     * Show all fields (including private) on this contract
     *
     * @return array
     */
    public static function all() : array
    {
        $cache = DataContract::getCache();
        $cacheKeyAll = static::makeCacheKey(Cache::ALL);

        if ($cache->has($cacheKeyAll)) {
            return $cache->get($cacheKeyAll);
        }

        $all = static::getInstance()
            ->properties
            ->keys()
            ->all();

        $cache->set($cacheKeyAll, $all);
        return $all;
    }

    /**
     * Get validation rules for this contract
     *
     * @see https://laravel.com/docs/8.x/validation#available-validation-rules
     * @return array
     */
    public static function validationRules() : array
    {
        $required = static::getInstance()->required->toArray();
        $rules = new Collection(static::validationRulesOptional());
        return $rules->map(function ($applied, $prop) use ($required) {
            if (in_array($prop, $required)) {
                $applied[] = 'required';
            }

            return $applied;
        })->all();
    }

    /**
     * Get any validation rules that are not required
     *
     * @return array
     */
    public static function validationRulesOptional(): array
    {
        $cache = DataContract::getCache();
        $cacheKeyRules = static::makeCacheKey(Cache::RULES);
        if ($cache->has($cacheKeyRules)) {
            return $cache->get($cacheKeyRules);
        }

        $rules = new Collection(RulesRegistry::all());
        $validationRules = static::getInstance()
            ->properties
            ->filter(function ($options, $prop) {
                // Remove the id field of this resource
                // Since it is likely to be auto-generated and we won't
                // need to manually validate them
                return $prop !== static::$id;
            })
            ->map(function ($options, $prop) use ($rules) {
                // Here we map through each remaining property and apply the relevant
                // rules to each one.
                //
                // There is some additional complexity due to modifiers like all_of and any_of,
                // which will mean that some properties have sub-properties which
                // will need to be gathered up here
                $property = new Property($prop, $options);
                $collection = new Collection([static::applyRules($property, $rules)]);

                if ($property->hasOption(Modifier::ALL_OF)) {
                    foreach ($property->getOption(Modifier::ALL_OF) as $allOf) {
                        $collection->push(static::applyRules(new Property($prop, $allOf), $rules));
                    }
                }

                return $collection;
            })
            ->map(function (Collection $ruleset) {
                // This is just flattening any sub-collections which were created
                // due to modifiers in the previous step
                return $ruleset->flatMap(function ($r) {
                    return $r;
                })->all();
            })
            ->all();

        $cache->set($cacheKeyRules, $validationRules);
        return $validationRules;
    }

    /**
     * Apply any rules that are relevant to the property
     *
     * @param Property $property
     * @param Collection $rules
     * @return array
     */
    private static function applyRules(Property $property, Collection $rules) : array
    {
        return $rules->map(function ($rule) use ($property) {
            return new $rule($property);
        })->filter(function ($rule) use ($property) {
            return $rule->check($property);
        })
        ->map(function ($rule) use ($property) {
            return $rule->getLaravelName($property);
        })
        ->values()
        ->all();
    }

    /**
     * Get validation rules for this contract with the specified rules excluded
     *
     * @param string|array $validationRules
     * @param string $rule
     * @return array
     */
    public static function rulesExcept($rule) : array
    {
        $rules = new Collection(static::validationRules());
        return $rules->map(function ($applied) use ($rule) {
            return (new Collection($applied))->filter(function ($r) use ($rule) {
                $r = strtok($r, ':');
                if (is_array($rule)) {
                    return !in_array($r, $rule);
                }

                return $r !== $rule;
            })->values()
            ->all();
        })->all();
    }

    /**
     * Compare against a Laravel Eloquent model and return a new array
     * containing only keys that are on the contract
     *
     * @param Eloquent $model
     * @return array
     */
    public static function fromEloquent(Eloquent $model) : array
    {
        $allowedKeys = array_flip(static::all());
        return array_intersect_key($model->attributesToArray(), $allowedKeys);
    }

    /**
     * Map a Laravel API resource against a data contract
     *
     * @param JsonResource $resource
     * @return array
     */
    public static function fromResource(JsonResource $resource) : array
    {
        $fields = static::all();
        // Map all fields that are in the contract to fields on
        // the resource
        return array_reduce($fields, function ($carry, $field) use ($resource) {
            $carry[$field] = $resource->$field;
            return $carry;
        }, []);
    }

    /**
     * Make a cache key for fetching/store a particular type of data
     *
     * @param string? $type
     * @return string
     */
    public static function makeCacheKey(string $type = null) : string
    {
        return implode(Cache::SEPARATOR, [
            $type,
            static::class,
        ]);
    }
}
