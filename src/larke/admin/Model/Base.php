<?php

declare (strict_types = 1);

namespace Larke\Admin\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

/*
 * Base
 *
 * @create 2020-10-23
 * @author deatil
 */
class Base extends Model
{
    public function scopeWithCertain($query, $relation, array $columns)
    {
        return $query->with([$relation => function ($query) use ($columns) {
            $query->select(array_merge(['id'], $columns));
        }]);
    }
    
    public function scopeWheres($query, array $columns)
    {
        if (empty($columns)) {
            return $query;
        }
        
        foreach ($columns as $column) {
            if (count($column) == 1) {
                $query->where(DB::raw($column[0]));
            } elseif (count($column) == 2) {
                $query->where($column[0], $column[1]);
            } elseif (count($column) == 3) {
                $query->where($column[0], $column[1], $column[2]);
            }
        }
        
        return $query;
    }
    
    public function scopeOrWheres($query, array $columns)
    {
        if (empty($columns)) {
            return $query;
        }
        
        foreach ($columns as $column) {
            if (count($column) == 1) {
                $query->orWhere(DB::raw($column[0]));
            } elseif (count($column) == 2) {
                $query->orWhere($column[0], $column[1]);
            } elseif (count($column) == 3) {
                $query->orWhere($column[0], $column[1], $column[2]);
            }
        }
        
        return $query;
    }
    
    /**
     * 活动
     */
    public function scopeActived($query)
    {
        return $query->where('status', '=', 1);
    }
    
    /**
     * 未活动
     */
    public function scopeInactived($query)
    {
        return $query->where('status', '=', 0);
    }
    
    /**
     * 启用
     */
    public function enable() 
    {
        return $this->update([
            'status' => 1,
        ]);
    }
    
    /**
     * 禁用
     */
    public function disable() 
    {
        return $this->update([
            'status' => 0,
        ]);
    }
    
    /**
     * 更改排序
     */
    public function updateListorder($listorder) 
    {
        return $this->update([
            'listorder' => $listorder,
        ]);
    }
    
    /**
     * 是否活动
     */
    public function isActive() 
    {
        return ($this->status == 1);
    }
    
}
