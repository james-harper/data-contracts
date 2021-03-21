<?php

namespace DataContracts\Tests;

use Mockery;
use Psr\SimpleCache\CacheInterface;
use DataContracts\Cache\Constants as Cache;
use DataContracts\DataContract;
use DataContracts\Tests\Constants\Group;

uses()->group(Group::CACHE);

class KingContract extends DataContract
{
    protected $schema = 'tests/data/person.json';
}

beforeEach(function () {
    /** @var Mockery\MockInterface|CacheInterface */
    $this->cache = Mockery::spy(CacheInterface::class);
    DataContract::setCache($this->cache);
});

it('caches repeated all calls', function () {
    $cacheKey = KingContract::makeCacheKey(Cache::ALL);
    // Make the initial all() call
    $data = KingContract::all();
    // The first call should have calculated the value and stored it
    //  in cache for subsequent calls
    $this->cache->shouldNotHaveReceived('get');
    $this->cache->shouldHaveReceived('set', [$cacheKey, $data])->once();

    // Configure spy for next call
    $this->cache->shouldReceive('has')->with($cacheKey)->andReturn(true);
    $this->cache->shouldReceive('get')->with($cacheKey)->andReturn($data);
    // Call all() for the 2nd time
    KingContract::all();
    // We expect that 2nd call was fetched from cache
    // And that we didn't need need to set() any more data in the cache
    $this->cache->shouldHaveReceived('get', [$cacheKey])->once();
    $this->cache->shouldHaveReceived('set', [$cacheKey, $data])->once();

    // Call all() a 3rd time just to ensure consistency
    KingContract::all();
    // Should have received from cache 2 times now
    // But still only have written to the cache once
    $this->cache->shouldHaveReceived('get', [$cacheKey])->twice();
    $this->cache->shouldHaveReceived('set', [$cacheKey, $data])->once();
});

it('caches repeated describe calls', function () {
    // describe() uses the cache for all() internally
    $cacheKey = KingContract::makeCacheKey(Cache::DESCRIBE);
    // Make the initial describe() call
    $data = KingContract::describe();
    // The first call should have calculated the value and stored it
    //  in cache for subsequent calls
    $this->cache->shouldNotHaveReceived('get');

    // Configure spy for next call
    $this->cache->shouldReceive('has')->with($cacheKey)->andReturn(true);
    $this->cache->shouldReceive('get')->with($cacheKey)->andReturn($data);
    // Call describe() for the 2nd time
    KingContract::describe();
    // We expect that 2nd call was fetched from cache
    // And that we didn't need need to set() any more data in the cache
    $this->cache->shouldHaveReceived('get', [$cacheKey])->once();

    // Call describe() a 3rd time just to ensure consistency
    KingContract::describe();
    // Should have received from cache 2 times now
    // But still only have written to the cache once
    $this->cache->shouldHaveReceived('get', [$cacheKey])->twice();
});

it('caches repeated validation rules calls', function () {
    $cacheKey = KingContract::makeCacheKey(Cache::RULES);
    // Make the initial all() call
    $data = KingContract::validationRulesOptional();
    // The first call should have calculated the value and stored it
    //  in cache for subsequent calls
    $this->cache->shouldNotHaveReceived('get');
    $this->cache->shouldHaveReceived('set', [$cacheKey, $data])->once();

    // Configure spy for next call
    $this->cache->shouldReceive('has')->with($cacheKey)->andReturn(true);
    $this->cache->shouldReceive('get')->with($cacheKey)->andReturn($data);
    // Call all() for the 2nd time
    KingContract::validationRulesOptional();
    // We expect that 2nd call was fetched from cache
    // And that we didn't need need to set() any more data in the cache
    $this->cache->shouldHaveReceived('get', [$cacheKey])->once();
    $this->cache->shouldHaveReceived('set', [$cacheKey, $data])->once();

    // Call all() a 3rd time just to ensure consistency
    KingContract::validationRulesOptional();
    // Should have received from cache 2 times now
    // But still only have written to the cache once
    $this->cache->shouldHaveReceived('get', [$cacheKey])->twice();
    $this->cache->shouldHaveReceived('set', [$cacheKey, $data])->once();
});
