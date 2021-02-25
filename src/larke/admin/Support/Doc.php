<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

/**
 * 类注释解析
 *
 * @create 2020-10-28
 * @author deatil
 */
Class Doc
{
    private $params = [];
    
    /**
     * 解析注释
     *
     * @param string $doc
     * @return array
     */
    public function parse(string $doc = ''): array
    {
        if (empty($doc)) {
            return $this->params;
        }
        
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return $this->params;
        }
        
        $comment = trim($comment[1]);
        
        if (preg_match_all ( '#^\s*\*(.*)#m', $comment, $lines) === false) {
            return $this->params;
        }
        
        $this->parseLines($lines[1]);
        
        return $this->params;
    }
    
    /**
     * 格式化数据
     *
     * @param array $lines
     * @return void
     */
    protected function parseLines(array $lines): void
    {
        $desc = [];
        
        foreach($lines as $line) {
            $parsedLine = $this->parseLine($line);
            
            if ($parsedLine === false 
                && ! isset($this->params['description'])
            ) {
                if (isset($desc)) {
                    $this->params['description'] = implode(PHP_EOL, $desc);
                }
                
                $desc = [];
            } elseif ($parsedLine !== false) {
                $desc[] = $parsedLine;
            }
        }
        
        $desc = implode(' ', $desc);
        
        if (! empty($desc)) {
            $this->params['long_description'] = $desc;
        }
    }
    
    /**
     * 格式化单行注释
     *
     * @param string $line
     * @return array|bool
     */
    protected function parseLine(string $line)
    {
        $line = trim($line);
        
        if (empty($line)) {
            return false;
        }
        
        if (strpos($line, '@') === 0) {
            if (strpos($line, ' ') > 0) {
                $param = substr($line, 1, strpos($line, ' ') - 1);
                $value = substr($line, strlen($param) + 2);
            } else {
                $param = substr($line, 1);
                $value = '';
            }
            
            if ($this->setParam($param, $value)) {
                return false;
            }
        }
        
        return $line;
    }
    
    /**
     * 设置数据
     *
     * @param string $lines
     * @param string $value
     * @return bool
     */
    protected function setParam(string $param, string $value): bool
    {
        if ($param == 'param' 
            || $param == 'header'
        ) {
            $value = $this->formatParam($value);
        }
        
        if ($param == 'class') {
            list ($param, $value) = $this->formatClass($value);
        }
        
        if ($param == 'return' 
            || $param == 'param' 
            || $param == 'header'
        ) {
            $this->params[$param][] = $value;
        } elseif (empty($this->params[$param])) {
            $this->params[$param] = $value;
        } else {
            $this->params[$param] = $this->params[$param] . $value;
        }
        
        return true;
    }
    
    /**
     * 格式化类
     *
     * @param string $value
     * @return array
     */
    protected function formatClass(string $value): array
    {
        $r = preg_split("[\(|\)]", $value);
        
        if (is_array($r)) {
            $param = $r[0];
            
            parse_str($r[1], $value);
            
            foreach ($value as $key => $val) {
                $val = explode(',', $val);
                
                if (count($val ) > 1) {
                    $value[$key] = $val;
                }
            }
        } else {
            $param = 'Unknown';
        }
        
        return [
            $param,
            $value
        ];
    }
    
    /**
     * 格式化
     *
     * @param string $string
     * @return array
     */
    protected function formatParam(string $string)
    {
        $string = $string." ";
        
        if (! preg_match_all('/(\w+):(.*?)[\s\n]/s', $string, $meatchs)) {
            return ''.$string;
        }
        
        $param = [];
        foreach ($meatchs[1] as $key => $value) {
            $param[$meatchs[1][$key]] = $this->getParamType($meatchs[2][$key]);
        }
        
        return $param;
    }
    
    /**
     * 转换类型
     *
     * @param string $type
     * @return array
     */
    protected function getParamType(string $type): string
    {
        $typeMaps = [
            'string' => '字符串',
            'int' => '整型',
            'float' => '浮点型',
            'boolean' => '布尔型',
            'date' => '日期',
            'array' => '数组',
            'fixed' => '固定值',
            'enum' => '枚举类型',
            'object' => '对象',
        ];
        return array_key_exists($type, $typeMaps) 
            ? $typeMaps[$type] 
            : $type;
    }
}