<?php

namespace Backpack\CRUD\Tests\Unit;

use Backpack\CRUD\app\Library\Support\SetupCache;
use Backpack\CRUD\Tests\BaseTestClass;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

/**
 * @covers Backpack\CRUD\app\Library\Support\SetupCache
 */
class SetupCacheTest extends BaseTestClass
{
    public function test_cached_setup_is_only_available_in_the_session_that_stored_it()
    {
        $cache = $this->makeCache();

        $this->useSession('firstSessionId00000000000000000000000000');
        $cache->store('table', ['setup' => 'value']);
        $this->assertSame(['setup' => 'value'], $cache->get('table'));

        $this->useSession('otherSessionId00000000000000000000000000');
        $this->assertNull($cache->get('table'));
        $this->assertFalse($cache->apply('table'));

        $this->useSession('firstSessionId00000000000000000000000000');
        $this->assertSame(['setup' => 'value'], $cache->get('table'));
    }

    private function useSession(string $id): void
    {
        $session = new Store('test', new ArraySessionHandler(10), $id);
        request()->setLaravelSession($session);
    }

    private function makeCache(): SetupCache
    {
        return new class extends SetupCache
        {
            protected function prepareDataForStorage(...$args)
            {
                return $args[0];
            }

            protected function applyFromCache($cachedData, ...$args)
            {
                return true;
            }
        };
    }
}
