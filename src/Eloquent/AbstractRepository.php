<?php
namespace CrCms\Repository\Eloquent;

use CrCms\Repository\Contracts\Eloquent\Eloquent;
use CrCms\Repository\Contracts\Eloquent\QueryMagic;
use CrCms\Repository\Contracts\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AbstractRepository
 * @package CrCms\Repository\Repositories
 */
abstract class AbstractRepository implements Repository,Eloquent
{

    /**
     * @var Builder
     */
    protected $query = null;

    /**
     * @var Model
     */
    protected $model = null;

    /**
     * @var array
     */
    protected $guards = [];


    /**
     * @return Model
     */
    abstract protected function model() : Model;


    /**
     * @return Model
     */
    public function newModel()
    {
        return $this->model();
    }


    /**
     * @return Model
     */
    public function getModel() : Model
    {
        if (!$this->model) {
            $this->model = $this->model();
        }
        return $this->model;
    }


    /**
     * @param array $guards
     * @return AbstractRepository
     */
    public function setGuards(array $guards) : AbstractRepository
    {
        $this->guards = $guards;
        return $this;
    }


    /**
     * @return array
     */
    public function getGuards() : array
    {
        return $this->guards;
    }


    /**
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }


    /**
     * @return Builder
     */
    protected function getNewQuery() : Builder
    {
        return $this->getModel()->newQuery();
    }


    /**
     * @return Builder
     */
    protected function getCurrentOrNewQuery() : Builder
    {
        $this->query ? : $this->query = $this->getNewQuery();
        return $this->query;
    }


    /**
     * @param array $data
     * @return array
     */
    protected function guardFilter(array $data) : array
    {
        if (empty($this->guards)) return $data;

        return array_filter($data,function($key) {
            return in_array($key,$this->guards,true);
        },ARRAY_FILTER_USE_KEY);
    }


    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        $data = $this->guardFilter($data);

        return $this->getModel()->create($data);
    }


    /**
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, int $id): Model
    {
        $data = $this->guardFilter($data);

        $model = $this->byId($id);
        foreach ($data as $key=>$value) {
            $model->{$key} = $value;
        }
        $model->save();

        return $model;
    }


    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return $this->getNewQuery()->where('id',$id)->delete();
    }


    /**
     * @param int $id
     * @param array $columns
     * @return Model
     */
    public function byId(int $id, array $columns = ['*']): Model
    {
        return $this->getNewQuery()->select($columns)->where($this->getModel()->getKeyName(),$id)->firstOrFail();
    }


    /**
     * @param string $field
     * @param string $value
     * @param array $columns
     * @return Model
     */
    public function oneBy(string $field, string $value, array $columns = ['*']): Model
    {
        return $this->getNewQuery()->select($columns)->where($field,$value)->firstOrFail();
    }


    /**
     * @param QueryMagic $queryMagic
     * @return Repository
     */
    public function byMagic(QueryMagic $queryMagic): Repository
    {
        $this->query = $queryMagic->magic($this->getCurrentOrNewQuery(),$this);
        return $this;
    }


    /**
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->getNewQuery()->select($columns)->orderBy($this->getModel()->getKeyName(),'desc')->get();
    }


    /**
     * @return int
     */
    public function count(): int
    {
        return $this->getNewQuery()->count();
    }


    /**
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->getNewQuery()->select($columns)->orderBy($this->getModel()->getKeyName(),'desc')->paginate($perPage);
    }


    /**
     * @param string $field
     * @param string $value
     * @param array $columns
     * @return Collection
     */
    public function by(string $field, string $value, array $columns = ['*']): Collection
    {
        return $this->getNewQuery()->select($columns)->where($field,$value)->orderBy($this->getModel()->getKeyName(),'desc')->get();
    }


    /**
     * @param callable $callable
     * @return Repository
     */
    public function byCallable(callable $callable): Repository
    {
        $this->query = call_user_func($callable,$this->getCurrentOrNewQuery());
        return $this;
    }


    /**
     * @param array $where
     * @param array $columns
     * @return Collection
     */
    public function byWhere(array $wheres, array $columns = ['*']): Collection
    {
        $this->query = (new ResolveWhereQuery)->getQuery($wheres,$this->getCurrentOrNewQuery());
        return $this->query->select($columns)->get();
    }
}