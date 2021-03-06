<?php

namespace Lib\DataProvider\Eloquent;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface as Database;
use Illuminate\Database\Eloquent\Model;
use Lib\DataProvider\Contracts\Criteria;
use Lib\DataProvider\Contracts\DataProvider as DataProviderContract;
use Lib\DataProvider\Exceptions\DataProviderDeleteException;
use Lib\DataProvider\Exceptions\DataProviderException;
use Lib\DataProvider\Exceptions\DataProviderNotFoundException;
use Lib\DataProvider\Exceptions\DataProviderSaveException;
use Throwable;

/**
 * Class EloquentDataProvider.
 *
 * @package Lib\DataProvider\Eloquent
 */
abstract class EloquentDataProvider implements DataProviderContract
{
    /**
     * Model class instance.
     *
     * @var mixed
     */
    protected $model;

    /**
     * Model query class instance.
     *
     * @var mixed
     */
    protected $query;

    /**
     * Database connection class instance.
     *
     * @var mixed
     */
    protected $database;

    /**
     * Events dispatcher class instance.
     *
     * @var mixed
     */
    protected $dispatcher;

    /**
     * DataProvider constructor.
     *
     * @param Database $database
     * @param Dispatcher $dispatcher
     */
    public function __construct(Database $database, Dispatcher $dispatcher)
    {
        $this->database = $database;
        $this->dispatcher = $dispatcher;

        $this->reset();
    }

    /**
     * Reset model class instance.
     *
     * @return void
     * @throws DataProviderException
     */
    protected function resetModel(): void
    {
        $modelClass = $this->getModelClass();

        if (!class_exists($modelClass)) {
            throw new DataProviderException(sprintf('The %s class does not exist.', $modelClass));
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new DataProviderException(sprintf('The model must be inherited from the %s class.', Model::class));
        }

        $this->model = new $modelClass;
    }

    /**
     * Reset model query class instance.
     *
     * @return void
     */
    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        $this->resetModel();
        $this->resetQuery();
    }

    /**
     * Assert model.
     *
     * @param mixed $model
     * @return void
     * @throws DataProviderException
     */
    protected function assertModel($model): void
    {
        $modelClass = $this->getModelClass();

        if (!($model instanceof $modelClass)) {
            throw new DataProviderException(sprintf('The model must be an instance of the %s class.', $modelClass));
        }
    }

    /**
     * Dispatch an event with name 'NameSpace\ClassName@eventName'.
     *
     * @param string $eventName
     * @param array $data
     * @return void
     */
    protected function dispatchEvent(string $eventName, &...$data): void
    {
        $this->dispatcher->dispatch(static::class . '@' . $eventName, $data);
    }

    /**
     * @inheritdoc
     */
    abstract public function getModelClass(): string;

    /**
     * @inheritdoc
     */
    public function applyCriteria(Criteria $criteria)
    {
        $criteria->apply($this->query);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function applyCriteriaWhen(bool $value, Criteria $criteria)
    {
        if ($value) {
            $this->applyCriteria($criteria);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getByKey($key, array $options = [])
    {
        $this->dispatchEvent('beforeGetByKey', $this->query, $options);
        $model = $this->query->find($key);
        if (is_null($model)) {
            throw new DataProviderNotFoundException(sprintf('%s not found.', class_basename($this->getModelClass())));
        }
        $this->dispatchEvent('afterGetByKey', $model, $options);
        $this->reset();

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getFirst(array $options = [])
    {
        $this->dispatchEvent('beforeGetFirst', $this->query, $options);
        $model = $this->query->first();
        if (is_null($model)) {
            throw new DataProviderNotFoundException(sprintf('%s not found.', class_basename($this->getModelClass())));
        }
        $this->dispatchEvent('afterGetFirst', $model, $options);
        $this->reset();

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function get(array $options = [])
    {
        $this->dispatchEvent('beforeGet', $this->query, $options);
        $models = $this->query->get();
        $this->dispatchEvent('afterGet', $models, $options);
        $this->reset();

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function exists(array $options = []): bool
    {
        $this->dispatchEvent('beforeExists', $this->query, $options);
        $exists = $this->query->exists();
        $this->dispatchEvent('afterExists', $exists, $options);
        $this->reset();

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function each(Closure $callback, int $chunkSize = 100, array $options = []): void
    {
        $this->query->chunk($chunkSize, function ($models) use ($callback, $options) {
            foreach ($models as $model) {
                $this->dispatchEvent('beforeEach', $this->query, $options);
                $callback($model);
                $this->dispatchEvent('afterEach', $model, $options);
            }
        });

        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function paginate(int $page = 1, int $perPage = 20, array $options = [])
    {
        $this->dispatchEvent('beforePaginate', $this->query, $options);
        $models = $this->query->paginate($perPage, ['*'], 'page', $page);
        $this->dispatchEvent('afterPaginate', $models, $options);
        $this->reset();

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function count(array $options = []): int
    {
        $this->dispatchEvent('beforeCount', $this->query, $options);
        $count = $this->query->count();
        $this->dispatchEvent('afterCount', $count, $options);
        $this->reset();

        return $count;
    }

    /**
     * @inheritdoc
     */
    public function save($model, array $attributes = [], array $options = []): void
    {
        $this->assertModel($model);

        try {
            $this->database->beginTransaction();
            $this->dispatchEvent('beforeSave', $model, $attributes, $options);
            $model->fill($attributes);
            $model->save();
            $this->dispatchEvent('afterSave', $model, $attributes, $options);
            $this->database->commit();
        } catch (Throwable $e) {
            $this->database->rollBack();
            throw new DataProviderSaveException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->reset();
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($model, array $options = []): void
    {
        $this->assertModel($model);

        try {
            $this->database->beginTransaction();
            $this->dispatchEvent('beforeDelete', $model, $options);
            $deleted = $model->delete();
            $this->dispatchEvent('afterDelete', $model, $deleted, $options);
            $this->database->commit();
        } catch (Throwable $e) {
            $this->database->rollBack();
            throw new DataProviderDeleteException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->reset();
        }
    }
}
