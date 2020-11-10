<?php

namespace Larke\Admin\Model;

use Illuminate\Database\Eloquent\Model;

/*
 * Base
 *
 * @create 2020-10-23
 * @author deatil
 */
class Base extends Model
{
    public function scopeWithCertain($query, $relation, Array $columns)
    {
        return $query->with([$relation => function ($query) use ($columns) {
            $query->select(array_merge(['id'], $columns));
        }]);
    }
    
    public function enable() 
    {
        return $this->update([
            'status' => 1,
        ]);
    }
    
    public function disable() 
    {
        return $this->update([
            'status' => 0,
        ]);
    }
    
    public function updateListorder($listorder) 
    {
        return $this->update([
            'listorder' => $listorder,
        ]);
    }
    
    public function isActive() 
    {
        return ($this->status == 1);
    }
    
}
