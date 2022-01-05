<?php

declare (strict_types = 1);

namespace Larke\Admin\Support;

/**
 * 脚本进度条
 *
 * @create 2022-1-5
 * @author deatil
 */
class ProgressBar
{
    // 前缀
    protected $prefix = '[x] ';

    // 后缀
    protected $suffix = '';

    // 进度条
    protected $progressString = '>';

    // 步行分割数
    protected $length = 100;

    // 总数
    protected $total = 0;

    // 当前数
    protected $current = 0;

    // 分割数量
    protected $average = 0;

    /**
     * 设置前缀
     *
     * @param $prefix 前缀
     * @return $this
     */
    public function withPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * 设置后缀
     *
     * @param $suffix 后缀
     * @return $this
     */
    public function withSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * 设置进度条
     *
     * @param $progressString 前缀
     * @return $this
     */
    public function withProgressString($progressString)
    {
        $this->progressString = $progressString;

        return $this;
    }

    /**
     * 设置步行分割数
     *
     * @param $length
     * @return $this
     */
    public function withLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * 设置总数
     *
     * @param $total
     * @return $this
     */
    public function withTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * 获取分割数量
     *
     * @return void
     */
    public function getAverage()
    {
        $this->average = $this->length / $this->total;
        
        return $this->average;
    }

    /**
     * 重置进度
     *
     * @return $this
     */
    public function resetBar()
    {
        $this->current = 1;
        
        return $this;
    }

    /**
     * 前进
     *
     * @param int $step
     * @return void
     */
    public function advance($step = 1)
    {
        // 当前进度小于总数时
        if ($this->current < $this->total) {
            $this->current += $step;
        }

        return $this->showBar();
    }

    /**
     * 显示进度条内容
     *
     * @param bool $end
     * @return string
     */
    protected function showBar()
    {
        // \r 用在双引号内，回车，回到当前行首
        $bar = $this->bar()  . "\r";
        
        return $bar;
    }

    /**
     * 进度条
     *
     * @return string
     */
    protected function bar()
    {
        // 取最小值
        $this->current = min($this->current, $this->total);
        
        $left = $this->total - $this->current;
        
        $average = $this->getAverage();

        $empty = str_repeat(' ', intval($left * $average));

        $bar = str_repeat($this->progressString, intval($this->current * $average));

        $percent = ((int)(sprintf('%.2f', $this->current / $this->total) * 100)) . '%';

        return $this->prefix . $bar . $empty . ' ' . $percent . $this->suffix;
    }
}