<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

/**
 * 树
 *
 * @create 2020-10-25
 * @author deatil
 */
class Tree
{
    /**
     * 生成树型结构所需要的2维数组
     *
     * @var array
     */
    public $data = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     *
     * @var array
     */
    public $icon = ['│', '├', '└'];
    public $blankspace = "&nbsp;";
    
    // 查询
    public $idKey = "id";
    public $parentidKey = "parentid";
    public $spacerKey = "spacer";
    public $depthKey = "depth";
    public $haschildKey = "haschild";
    
    // 返回子级key
    public $buildChildKey = "child";
    
    /**
     * 创建
     */
    public static function create()
    {
        return new static();
    }

    /**
     * 设置配置
     *
     * @param   array  $key     键值
     * @param   string $value   内容
     * @return array
     */
    public function withConfig($key, $value)
    {
        if (isset($this->{$key})) {
            $this->{$key} = $value;
        }
        return $this;
    }

    /**
     * 构造函数，初始化类
     *
     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','parentid'=>0,'title'=>'一级栏目一'),
     *      2 => array('id'=>'2','parentid'=>0,'title'=>'一级栏目二'),
     *      3 => array('id'=>'3','parentid'=>1,'title'=>'二级栏目一'),
     *      4 => array('id'=>'4','parentid'=>1,'title'=>'二级栏目二'),
     *      5 => array('id'=>'5','parentid'=>2,'title'=>'二级栏目三'),
     *      6 => array('id'=>'6','parentid'=>3,'title'=>'三级栏目一'),
     *      7 => array('id'=>'7','parentid'=>3,'title'=>'三级栏目二')
     * )
     */
    public function withData($data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 构建数组
     *
     * @param   string  $id 要查询的ID
     * @param   string  $itemprefix 前缀
     * @return  string
     */
    public function build($id = 0, $itemprefix = '', $depth = 0)
    {
        $child = $this->getListChild($this->data, $id);
        if (!is_array($child)) {
            return [];
        }
        
        $data = [];
        $number = 1;
        
        $total = count($child);
        foreach ($child as $id => $value) {
            $info = $value;
            
            $j = $k = '';
            if ($number == $total) {
                if (isset($this->icon[2])) {
                    $j .= $this->icon[2];
                }
                $k = $itemprefix ? $this->blankspace : '';
            } else {
                if (isset($this->icon[1])) {
                    $j .= $this->icon[1];
                }
                $k = $itemprefix ? (isset($this->icon[0]) ? $this->icon[0] : '') : '';
            }
            $spacer = $itemprefix ? $itemprefix . $j : '';
            $info[$this->spacerKey] = $spacer;
            
            // 深度
            $info[$this->depthKey] = $depth;
            
            $childList = $this->build($value[$this->idKey], $itemprefix . $k . $this->blankspace, $depth + 1);
            if (!empty($childList)) {
                $info[$this->buildChildKey] = $childList;
            }
            
            $data[] = $info;
            $number++;
        }
        
        return $data;
    }

    /**
     * 所有父节点
     *
     * @param   array       $list       数据集
     * @param   string|int  $parentid   节点的parentid
     * @param   string      $sort       排序
     * @return  array
     */
    public function getListParents($list = [], $parentid = '', $sort = 'desc')
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }
        
        $result = [];
        foreach ($list as $value) {
            if ((string) $value[$this->idKey] == (string) $parentid) {
                $result[] = $value;
                
                $parent = $this->getListParents($list, $value[$this->parentidKey], $sort);
                if (!empty($parent)) {
                    if ($sort == 'asc') {
                        $result = array_merge($result, $parent);
                    } else {
                        $result = array_merge($parent, $result);
                    }
                }
            }
        }
        
        return $trees;
    }

    /**
     * 所有父节点的ID列表
     *
     * @param   array       $list       数据集
     * @param   string|int  $parentid   节点的parentid
     * @return  array
     */
    public function getListParentsId($list = [], $parentid = '')
    {
        $parents = $this->getListParents($list, $parentid);
        if (empty($parents)) {
            return [];
        }
        
        $ids = [];
        foreach ($parents as $parent) {
            $ids[] = $parent[$this->idKey];
        }
        
        return $ids;
    }

    /**
     * 获取当前ID的所有子节点
     *
     * @param   array       $list   数据集
     * @param   string|int  $id     当前id
     * @param   string      $sort   排序
     * @return  array
     */
    public function getListChildren($list = [], $id = '', $sort = 'desc')
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }
        
        $result = [];
        foreach ($list as $value) {
            if ((string) $value[$this->parentidKey] == (string) $id) {
                $result[] = $value;
                
                $child = $this->getListChildren($list, $value[$this->idKey], $sort);
                if (!empty($child)) {
                    if ($sort == 'asc') {
                        $result = array_merge($result, $child);
                    } else {
                        $result = array_merge($child, $result);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取当前 ID 的所有子节点 id
     *
     * @param   array       $list   数据集
     * @param   string|int  $id     当前id
     * @return  array
     */
    public function getListChildrenId($list = [], $id = '')
    {
        $childs = $this->getListChildren($list, $id);
        if (empty($childs)) {
            return [];
        }
        
        $ids = [];
        foreach ($childs as $child) {
            $ids[] = $child[$this->idKey];
        }
        
        return $ids;
    }

    /**
     * 得到子级第一级数组
     *
     * @param   array       $list   数据集
     * @param   string|int  $id     当前id
     * @return  array
     */
    public function getListChild($list = [], $id)
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }
        
        $id = (string) $id;
        $newData = [];
        foreach ($list as $key => $data) {
            $dataParentId = (string) $data[$this->parentidKey];
            if ($dataParentId == $id) {
                $newData[$key] = $data;
            }
        }
        
        return $newData;
    }

    /**
     * 获取ID自己的数据
     *
     * @param   array       $list   数据集
     * @param   string|int  $id     当前id
     * @return  array
     */
    public function getListSelf($list = [], $id)
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }
        
        $id = (string) $id;
        foreach ($list as $key => $data) {
            $dataId = (string) $data[$this->idKey];
            if ($dataId == $id) {
                return $data;
            }
        }
        
        return [];
    }

    /**
     * 将 build 的结果返回为二维数组
     *
     * @param   array   $data       数据
     * @param   int     $parentid   父级ID
     * @return  array
     */
    public function buildFormatList($data = [], $parentid = 0)
    {
        if (empty($data)) {
            return [];
        }
        
        $list = [];
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                if (!isset($v[$this->spacerKey])) {
                    $v[$this->spacerKey] = '';
                }
                
                $child = isset($v[$this->buildChildKey]) ? $v[$this->buildChildKey] : [];
                $v[$this->haschildKey] = $child ? 1 : 0;
                unset($v[$this->buildChildKey]);
                
                if (! isset($v[$this->parentidKey])) {
                    $v[$this->parentidKey] = $parentid;
                }
                
                $list[] = $v;

                if (! empty($child)) {
                    $list = array_merge($list, $this->buildFormatList($child, $v[$this->idKey]));
                }
            }
        }
        
        return $list;
    }

}
