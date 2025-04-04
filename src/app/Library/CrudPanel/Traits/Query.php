<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;

trait Query
{
    /** @var Builder */
    public $query;

    /** @var Builder */
    public $totalQuery;

    // ----------------
    // ADVANCED QUERIES
    // ----------------

    /**
     * Add another clause to the query (for ex, a WHERE clause).
     *
     * Examples:
     * $this->crud->addClause('active');
     * $this->crud->addClause('type', 'car');
     * $this->crud->addClause('where', 'name', '=', 'car');
     * $this->crud->addClause('whereName', 'car');
     * $this->crud->addClause('whereHas', 'posts', function($query) {
     *     $query->activePosts();
     * });
     *
     * @param  callable|string  $function
     * @return mixed
     */
    public function addClause($function)
    {
        if ($function instanceof \Closure) {
            $function($this->query);

            return $this->query;
        }

        return call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1));
    }

    /**
     * This function is an alias of `addClause` but also adds the query as a constrain
     * in the `totalQuery` property.
     *
     * @param  \Closure|string  $function
     * @return self
     */
    public function addBaseClause($function)
    {
        if ($function instanceof \Closure) {
            $function($this->query);
            $function($this->totalQuery);

            return $this;
        }
        call_user_func_array([$this->query, $function], array_slice(func_get_args(), 1));
        call_user_func_array([$this->totalQuery, $function], array_slice(func_get_args(), 1));

        return $this;
    }

    public function setQuery(QueryBuilder $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Use eager loading to reduce the number of queries on the table view.
     *
     * @param  array|string  $entities
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function with($entities)
    {
        return $this->query->with($entities);
    }

    /**
     * Order the results of the query in a certain way.
     *
     * @param  string  $field
     * @param  string  $order
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderBy($field, $order = 'asc')
    {
        if ($this->getRequest()->has('order')) {
            return $this->query;
        }

        return $this->query->orderBy($field, $order);
    }

    /**
     * Order results of the query in a custom way.
     *
     * @param  array  $column  Column array with all attributes
     * @param  string  $column_direction  ASC or DESC
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customOrderBy($column, $columnDirection = 'asc')
    {
        if (! isset($column['orderLogic'])) {
            return $this->query;
        }

        $orderLogic = $column['orderLogic'];

        if ($orderLogic instanceof \Closure) {
            return $orderLogic($this->query, $column, $columnDirection);
        }

        return $this->query;
    }

    /**
     * Group the results of the query in a certain way.
     *
     * @param  string  $field
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function groupBy($field)
    {
        return $this->query->groupBy($field);
    }

    /**
     * Limit the number of results in the query.
     *
     * @param  int  $number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function limit($number)
    {
        return $this->query->limit($number);
    }

    /**
     * Take a certain number of results from the query.
     *
     * @param  int  $number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function take($number)
    {
        return $this->query->take($number);
    }

    /**
     * Start the result set from a certain number.
     *
     * @param  int  $number
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function skip($number)
    {
        return $this->query->skip($number);
    }

    /**
     * Count the number of results.
     *
     * @return int
     */
    public function count()
    {
        return $this->query->count();
    }

    /**
     * Apply table prefix in the order clause if the query contains JOINS clauses.
     *
     * @param  string  $column_name
     * @param  string  $column_direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderByWithPrefix($column_name, $column_direction = 'asc')
    {
        $column_direction = strtolower($column_direction);

        if ($this->query->getQuery()->joins !== null) {
            return $this->query->orderBy("{$this->model->getTableWithPrefix()}.{$column_name}", $column_direction);
        }

        return $this->query->orderBy($column_name, $column_direction);
    }

    /**
     * Get the entries count from `totalQuery`.
     *
     * @return int
     */
    public function getTotalQueryCount()
    {
        if (! $this->getOperationSetting('showEntryCount')) {
            return 0;
        }

        return  $this->getOperationSetting('totalEntryCount') ??
                $this->getCountFromQuery($this->totalQuery);
    }

    /**
     * Get the entries count from the `query`.
     *
     * @return int
     */
    public function getQueryCount()
    {
        return $this->getCountFromQuery($this->query);
    }

    /**
     * Return the filtered query count or skip the counting when the `totalQuery` is the same as the filtered one.
     *
     * @return int|null
     */
    public function getFilteredQueryCount()
    {
        // check if the filtered query is different from total query, in case they are the same, skip the count
        $filteredQuery = $this->query->toBase()->cloneWithout(['orders', 'limit', 'offset']);

        return $filteredQuery->toSql() !== $this->totalQuery->toSql() ? $this->getQueryCount() : null;
    }

    /**
     * Do a separate query to get the total number of entries, in an optimized way.
     *
     * @param  Builder  $query
     * @return int
     */
    private function getCountFromQuery(Builder $query)
    {
        if (! $this->driverIsSql()) {
            return $query->count();
        }

        $crudQuery = $query->toBase()->clone();

        $modelTable = $this->model->getTable();

        // create an "outer" query, the one that is responsible to do the count of the "crud query".
        $outerQuery = $crudQuery->newQuery();

        // add the count query in the "outer" query.
        $outerQuery = $outerQuery->selectRaw('count(*) as total_rows');

        // Expression columns are hand-written by developers in ->selectRaw() and we can't exclude those statements reliably
        // so we just store them and re-use them in the sub-query too.
        $expressionColumns = [];

        foreach ($crudQuery->columns ?? [] as $column) {
            if (! is_string($column) && is_a($column, 'Illuminate\Database\Query\Expression')) {
                $expressionColumns[] = $column;
            }
        }
        // add the subquery from where the "outer query" will count the results.
        // this subquery is the "main crud query" without some properties:
        // - columns : we manually select the "minimum" columns possible from database.
        // - orders/limit/offset because we want the "full query count" where orders don't matter and limit/offset would break the total count
        $subQuery = $crudQuery->cloneWithout(['columns', 'orders', 'limit', 'offset']);

        // select minimum possible columns for the count
        $minimumColumns = ($crudQuery->groups || $crudQuery->havings) ? $modelTable.'.*' : $modelTable.'.'.$this->model->getKeyName();
        $subQuery->select($minimumColumns);

        // in case there are raw expressions we need to add them too.
        foreach ($expressionColumns as $expression) {
            $subQuery->selectRaw($expression);
        }

        // re-set the previous query bindings
        foreach ($crudQuery->getRawBindings() as $type => $binding) {
            $subQuery->setBindings($binding, $type);
        }

        $outerQuery = $outerQuery->fromSub($subQuery, str_replace('.', '_', $modelTable).'_aggregator');

        return $outerQuery->cursor()->first()->total_rows;
    }
}
