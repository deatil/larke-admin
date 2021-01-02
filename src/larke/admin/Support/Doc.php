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

    private $params = array ();
    
    /**
     * 解析注释
     * @param string $doc
     * @return array
     */
    public function parse($doc = '') 
    {
        if ($doc == '' || !$doc) {
            return $this->params;
        }
        // Get the comment
        if (preg_match ( '#^/\*\*(.*)\*/#s', $doc, $comment ) === false)
            return $this->params;
        $comment = trim ( $comment [1] );
        // Get all the lines and strip the * from the first character
        if (preg_match_all ( '#^\s*\*(.*)#m', $comment, $lines ) === false)
            return $this->params;
        $this->parseLines ( $lines [1] );
        return $this->params;
    }
    
    private function parseLines($lines) 
    {
        $desc = [];
        foreach ( $lines as $line ) {
            $parsedLine = $this->parseLine ( $line ); // Parse the line
            if ($parsedLine === false && ! isset ( $this->params ['description'] )) {
                if (isset ( $desc )) {
                    // Store the first line in the short description
                    $this->params ['description'] = implode ( PHP_EOL, $desc );
                }
                $desc = array ();
            } elseif ($parsedLine !== false) {
                $desc [] = $parsedLine; // Store the line in the long description
            }
        }
        $desc = implode ( ' ', $desc );
        if (! empty ( $desc ))
            $this->params ['long_description'] = $desc;
    }
    
    private function parseLine($line) 
    {
        // trim the whitespace from the line
        $line = trim ( $line );
        if (empty ( $line ))
            return false; // Empty line
        if (strpos ( $line, '@' ) === 0) {
            if (strpos ( $line, ' ' ) > 0) {
                // Get the parameter name
                $param = substr ( $line, 1, strpos ( $line, ' ' ) - 1 );
                $value = substr ( $line, strlen ( $param ) + 2 ); // Get the value
            } else {
                $param = substr ( $line, 1 );
                $value = '';
            }
            // Parse the line and return false if the parameter is valid
            if ($this->setParam ( $param, $value ))
                return false;
        }
        return $line;
    }
    
    private function setParam($param, $value) 
    {
        if ($param == 'param' || $param == 'header')
            $value = $this->formatParam( $value );
        if ($param == 'class')
            list ( $param, $value ) = $this->formatClass ( $value );
        if($param == 'return' || $param == 'param' || $param == 'header'){
            $this->params [$param][] = $value;
        }else if (empty ( $this->params [$param] )) {
            $this->params [$param] = $value;
        } else {
            $this->params [$param] = $this->params [$param] . $value;
        }
        return true;
    }
    
    private function formatClass($value) 
    {
        $r = preg_split ( "[\(|\)]", $value );
        if (is_array ( $r )) {
            $param = $r [0];
            parse_str ( $r [1], $value );
            foreach ( $value as $key => $val ) {
                $val = explode ( ',', $val );
                if (count ( $val ) > 1)
                    $value [$key] = $val;
            }
        } else {
            $param = 'Unknown';
        }
        return array (
            $param,
            $value
        );
    }
    
    private function formatParam($string) 
    {
        $string = $string." ";
        if(preg_match_all('/(\w+):(.*?)[\s\n]/s', $string, $meatchs)){
            $param = [];
            foreach ($meatchs[1] as $key=>$value){
                $param[$meatchs[1][$key]] = $this->getParamType($meatchs[2][$key]);
            }
            return $param;
        }else{
            return ''.$string;
        }
    }
    
    private function getParamType($type)
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
        return array_key_exists($type,$typeMaps) ? $typeMaps[$type] : $type;
    }
}