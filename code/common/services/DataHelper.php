<?php

namespace app\common\services;

class DataHelper {
	/**
     * 根据某个字段 in  查询
     */
    public static function getDicByRelateID($data,$relate_model,$id_column,$pk_column,$name_columns = [])
    {
        $_ids = [];
        $_names = [];
        //遍历查询出来的地址
        foreach($data as $_row)
        {
            //将每个地址的地区id添加到ids中
            $_ids[] = $_row[$id_column];
        }
        //使用城市类查询所有的地区id
        $rel_data = $relate_model::findAll([$pk_column => array_unique($_ids)]);
        //查到之后遍历
        foreach($rel_data as $_rel)
        {
            $map_item = [];
            //如果省市县存在并且是个数组
            if($name_columns && is_array($name_columns)){
                //遍历出来,将城市中对应的省市县存到map_item中
                foreach($name_columns as $name_column){
                    $map_item[$name_column] = $_rel->$name_column;
                }
            }
            //存到_names中
            $_names[$_rel->$pk_column] = $map_item;
        }
        //返回
        return $_names;
    }


}
