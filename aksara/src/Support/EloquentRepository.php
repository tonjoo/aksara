<?php

namespace Aksara\Support;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository
{
    protected $model;

    public function find($id)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return false;
        }
        return $data;
    }

    public function storeRelation($id, $relationName, Request $request)
    {
        $data = $this->find($id);
        if (!$data) {
            return false;
        }

        if ($data->$relationName() instanceof HasOne) {
            $data->$relationName->fill($request->input());
            return $data->$relationName->save();
        }
        throw new \Exception('Relation type not supported');
    }

    public function attach($id, $relationName, $attachId, $once = false)
    {
        $data = $this->find($id);
        if (!$data) {
            return false;
        }
        if ($data->$relationName() instanceof BelongsToMany) {
            if (!$once || !$data->$relationName->contains($attachId)) {
                $data->$relationName()->attach($attachId);
            }
            return true;
        }
        throw new \Exception('Relation type not supported');
    }

    public function attachOnce($id, $relationName, $attachId)
    {
        return $this->attach($id, $relationName, $attachId, true);
    }

    public function detach($id, $relationName, $detachId)
    {
        $data = $this->find($id);
        if (!$data) {
            return false;
        }
        if ($data->$relationName() instanceof BelongsToMany) {
            if ($data->$relationName->contains($detachId)) {
                $data->$relationName()->detach($detachId);
            }
            return true;
        }
        throw new \Exception('Relation type not supported');
    }

    public function store(Request $request)
    {
        $data = $this->model->create($request->input());
        if (!$data) {
            return false;
        }
        return $data;
    }

    public function update($id, Request $request)
    {
        $data = $this->find($id);
        if (!$data) {
            return false;
        }
        $data->fill($request->input());
        $data->save();
        return $data;
    }

    /**
     * @param $id int|array
     */
    public function delete($id)
    {
        //have to check whether the $model instance is type of Eloquent Model
        //because it can be Query Builder instance depending on the caller
        if (!($this->model instanceof Model)) {
            $model = $this->model->getModel();
        } else {
            $model = $this->model;
        }
        //use destroy method, because we already know the primary key
        //and the method supports mass delete
        return $model->destroy($id);
    }

    public function sortCallback($callback, $order = 'ASC', $referenceModel = null)
    {
        $data = $referenceModel ?? $this->model;

        if (is_callable($callback)) {
            return $callback($data, $order);
        }

        throw new \Exception('Sort callback does not exist');
    }

    public function sort($column, $order = 'ASC', $referenceModel = null)
    {
        $data = $referenceModel ?? $this->model;
        return $data->orderBy($column, $order);
    }

    public function new($attributes = [])
    {
        $data = new $this->model;
        if (!empty($attributes)) {
            $data->fill($attributes);
        }
        return $data;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function allDetached($relationName, $relationId)
    {
        $result = $this->model->whereDoesntHave(
            $relationName, function ($query) use ($relationName, $relationId) {
                return $query->where($relationName.'.id', $relationId);
            });
        return $result->get();
    }

    public function search($columns, $value, $referenceModel = null)
    {
        $data = $referenceModel ?? $this->model;

        if (empty($columns) || empty($value)) {
            return $data->all();
        }

        $data = $data->where(function ($query) use ($columns, $value) {
            $first = true;
            foreach ($columns as $field) {
                if ($first) {
                    $query = $query->where($field, 'like', '%' . $value . '%');
                    $first = false;
                } else {
                    $query = $query->orWhere($field, 'like', '%' . $value . '%');
                }
            }
        });

        return $data;
    }

    public function filter($callback, $referenceModel = null)
    {
        return $callback($referenceModel ?? $this->model);
    }

    public function filterColumn($columnName, $value, $referenceModel = null)
    {
        $data = $referenceModel ?? $this->model;

        if (empty($value)) {
            return $data;
        }

        return $data->where($columnName, '=', $value);
    }

    public function between($columnName, $from, $to, $referenceModel = null)
    {
        $data = $referenceModel ?? $this->model;
        return $data->whereBetween($columnName, [ $from, $to ]);
    }
}
