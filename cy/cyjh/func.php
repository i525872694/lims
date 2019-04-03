<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 17-6-24
 * Time: 下午5:28
 */

function cyjh_data_from_excel($path,$years)
{


    $excel = PHPExcel_IOFactory::load($path);
    $data = $excel->getActiveSheet()->toArray(null,true,true,true);
    unset($data[1]);

    $fitter_key = ['序号','全年次数'];
    $have_key = ['站名'];
    $map_key = ['站名'=>'sname'];



    $head_data = $data[2];

    unset($data[2]);
//整理头信息
    $field_type = [];
    foreach ($head_data as $key=>$value)
    {

        $value=strtr($value,[' '=>'']);


        if(in_array($value,$have_key)){
            $field_type['have_value'][$key]=$map_key[$value];
        }
        else if(in_array($value,$fitter_key)){
            $field_type['delete_key'][$key]=1;
        }else{
            $field_type['data_value'][$key]='jhdate';
        }
    }

//第三行开始就是数据
    $result = [];
    foreach ($data as $line_info)
    {
        $line_info=array_filter($line_info);
        if(count($line_info)<2) continue;//无效行

        $tmp=[];
        foreach ($line_info as $key=>$value)
        {
            if($field_type['delete_key'][$key]){
                continue;
            }

            $value=strtr($value,[' '=>'']);

            if(!$value) continue;


            if($field_type['have_value'][$key])
            {
                $tmp[$field_type['have_value'][$key]]=$value;
            }else{
                $fenge = explode('.',$value);


                $month = str_pad($fenge[0],2,0,STR_PAD_LEFT);
                if(strlen( $fenge[1])>2)
                {
                    $day = substr($fenge[1],0,2);
                }else{
                    $day = str_pad($fenge[1],2,0,STR_PAD_LEFT);
                }

                $tmp['jhdate']=$years.'-'.$month.'-'.$day;
                $result[$tmp['sname'].':'.$tmp['jhdate']]=$tmp;
            }

        }
    }
    return $result;
}