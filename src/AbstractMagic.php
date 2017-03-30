<?php
namespace CrCms\Repository;

use CrCms\Repository\Contracts\QueryMagic;
use CrCms\Repository\Contracts\QueryRelate;
use CrCms\Repository\Contracts\Repository;

/**
 * Class AbstractMagic
 * @package CrCms\Repository\Eloquent
 */
 class AbstractMagic implements QueryMagic
{
    /**
     * @var array
     */
    protected $data = [];


    /**
     * @param array $data
     */
    public function setData(array $data) : self
    {
        $this->data = $data;
        return $this;
    }


    /**
     * @param QueryRelate $queryRelate
     * @param Repository $repository
     * @return QueryRelate
     */
    public function magic(QueryRelate $queryRelate, AbstractRepository $repository): QueryRelate
    {
        return $this->magicSearch($this->data,$queryRelate);
    }


     /**
      * @param array $data
      * @param QueryRelate $queryRelate
      * @return QueryRelate
      */
    protected function magicSearch(array $data, QueryRelate $queryRelate) : QueryRelate
    {
        return $this->dispatch($this->filter($data),$queryRelate);
    }


    /**
     * @param array $data
     * @param QueryRelate $queryRelate
     * @return QueryRelate
     */
    protected function dispatch(array $data, QueryRelate $queryRelate) : QueryRelate
    {
        foreach ($data as $key=>$item) {
            $item = is_array($item) ? $item : trim($item);
            $method = 'by'.studly_case($key);

            if (method_exists($this,$method)) {
                $queryRelate = call_user_func_array([$this,$method],[$queryRelate,$item]);
            }
        }
        return $queryRelate;
    }


    /**
     * @param array $data
     * @return array
     */
     protected function filter(array $data) : array
     {
         return array_filter($data,function($item){
             if (is_array($item)) {
                 return $this->filter($item);
             }
             $item = trim($item);
             //防止字符串'0'
             return (is_numeric($item) || !empty($item));
         });
     }
}