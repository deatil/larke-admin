<?php

namespace Larke\Admin\Auth;

/*
 * 管理员信息
 *
 * @create 2020-10-26
 * @author deatil
 */
class Admin
{
    /*
     * id
     */
    protected $id = null;
    
    /*
     * data
     */
    protected $data = [];
    
    /*
     * 设置 id
     */
    public function withId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /*
     * 设置 data
     */
    public function withData($data)
    {
        $this->data = $data;
        return $this;
    }
    
    /*
     * 获取 id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /*
     * 获取 data
     */
    public function getData()
    {
        return $this->data;
    }

}
