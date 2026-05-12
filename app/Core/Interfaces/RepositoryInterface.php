<?php

namespace App\Core\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * RepositoryInterface
 *
 * Contract every repository must fulfill.
 * Implementations live in app/Repositories/ (global) or
 * app/Modules/{Module}/Repositories/ (module-scoped).
 */
interface RepositoryInterface
{
    public function all(array $columns = ['*'], array $with = []): Collection;

    public function paginate(int $perPage = 15, array $columns = ['*'], array $with = []): LengthAwarePaginator;

    public function find(int|string $id, array $columns = ['*'], array $with = []): ?Model;

    public function findOrFail(int|string $id, array $columns = ['*'], array $with = []): Model;

    public function create(array $data): Model;

    public function update(int|string $id, array $data): Model;

    public function delete(int|string $id): bool;

    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    public function findAllBy(string $field, mixed $value, array $columns = ['*']): Collection;

    public function count(array $conditions = []): int;

    public function exists(array $conditions): bool;
}
