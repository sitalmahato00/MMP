<?php

namespace App\Core\Base;

use App\Core\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * BaseRepository
 *
 * Generic Eloquent repository. All module repositories extend this.
 * Provides standard CRUD, pagination and finder methods.
 *
 * Usage:
 *   class StudentRepository extends BaseRepository implements StudentRepositoryInterface {
 *       public function __construct(Student $model) { parent::__construct($model); }
 *   }
 */
abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(protected Model $model) {}

    public function all(array $columns = ['*'], array $with = []): Collection
    {
        return $this->model->with($with)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $with = []): LengthAwarePaginator
    {
        return $this->model->with($with)->paginate($perPage, $columns);
    }

    public function find(int|string $id, array $columns = ['*'], array $with = []): ?Model
    {
        return $this->model->with($with)->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*'], array $with = []): Model
    {
        $record = $this->find($id, $columns, $with);

        if (! $record) {
            throw (new ModelNotFoundException)->setModel(get_class($this->model), $id);
        }

        return $record;
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    public function findAllBy(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->where($field, $value)->get($columns);
    }

    public function count(array $conditions = []): int
    {
        $q = $this->model->newQuery();
        foreach ($conditions as $field => $value) {
            $q->where($field, $value);
        }

        return $q->count();
    }

    public function exists(array $conditions): bool
    {
        return $this->model->where($conditions)->exists();
    }

    /** Expose query builder for complex queries in child repos. */
    protected function query()
    {
        return $this->model->newQuery();
    }
}
