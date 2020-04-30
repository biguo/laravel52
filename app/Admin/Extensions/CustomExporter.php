<?php

namespace App\Admin\Extensions;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Exporters\AbstractExporter;

class CustomExporter extends AbstractExporter
{
    protected $rows;
    protected $map;

    public function __construct($rows = [], $map = [], Grid $grid = null)
    {
        parent::__construct($grid);
        $this->rows = $rows;
        $this->map = $map;
    }


    public function export()
    {
        $filename = $this->getTable() .'_'.time().'.csv';

        $headings = '';
        $content = '';
        foreach ($this->getData() as $idx => $item){
            $str = '';
            if(!empty($this->rows)){
                $item = array_only($item, $this->rows);
            }
            foreach ($item as $key => $value ){
                if(!empty($this->map) && isset($this->map[$key])){
                    $value = $this->map[$key][$value];
                }
                $str .= $value . ',';
                if($idx === 0) {
                    $headings .= $key . ',';
                }
            }
            $content .= rtrim($str, ',') ."\r\n";
            if($idx === 0) {
                $headings = rtrim($headings, ',')."\r\n";
            }
        }

        $output = $headings . $content;

        // 在这里控制你想输出的格式,或者使用第三方库导出Excel文件
        $headers = [
            'Content-Encoding'    => 'UTF-8',
            'Content-Type'        => 'text/csv;charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        // 导出文件，
        response(rtrim($output, "\n"), 200, $headers)->send();

        exit;
    }
}