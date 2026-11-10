<?php

namespace Backpack\CRUD\Tests\Unit;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\Support\DatatableCache;
use Backpack\CRUD\app\Library\Support\SetupCache;
use Backpack\CRUD\Tests\BaseTestClass;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

/**
 * @covers Backpack\CRUD\app\Library\Support\SetupCache
 * @covers Backpack\CRUD\app\Library\Support\DatatableCache
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

    public function test_cached_back_url_is_only_available_in_the_session_that_stored_it()
    {
        request()->merge(['datatable_id' => 'table']);

        $this->useSession('firstSessionId00000000000000000000000000');
        DatatableCache::cacheBackToAllEntriesUrl('table', 'https://example.com/back');

        $this->useSession('otherSessionId00000000000000000000000000');
        $crud = $this->makeCrud();
        DatatableCache::applyFromRequest($crud);
        $this->assertNull($crud->getOperationSetting('backToAllEntriesUrl'));

        $this->useSession('firstSessionId00000000000000000000000000');
        $crud = $this->makeCrud();
        DatatableCache::applyFromRequest($crud);
        $this->assertSame('https://example.com/back', $crud->getOperationSetting('backToAllEntriesUrl'));
    }

    private function useSession(string $id): void
    {
        $session = new Store('test', new ArraySessionHandler(10), $id);
        request()->setLaravelSession($session);
    }

    private function makeCrud(): CrudPanel
    {
        $crud = new CrudPanel();
        $crud->setOperation('list');

        return $crud;
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
