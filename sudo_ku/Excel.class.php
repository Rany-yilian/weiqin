<?php  
	class Excel{
    private $head;
    private $body;
     
    /**
     * 
     * @param type $arr 一维数组
     */
    public function addHeader($arr){
        foreach($arr as $headVal){
            $headVal = $this->charset($headVal);
            $this->head .= "{$headVal}\t ";
        }
        $this->head .= "\n";
    }
     
    /**
     * 
     * @param type $arr 二维数组
     */
    public function addBody($arr){
        foreach($arr as $arrBody){
            foreach($arrBody as $bodyVal){
                $bodyVal = $this->charset($bodyVal);
                // 过滤特殊字符
                $bodyVal = str_replace(array('\\n','\\t','<br>','<br />', '<br>','</br>'), "", $bodyVal);
                $bodyVal = str_replace(array("rn", "r", "n"), "", $bodyVal);
                $bodyVal =preg_replace("{\t}","",$bodyVal);
                $bodyVal=preg_replace("{\r\n}","",$bodyVal);
                $bodyVal=preg_replace("{\r}","",$bodyVal);
                $bodyVal=preg_replace("{\n}","",$bodyVal);
                $bodyVal = str_replace(",", " ",$bodyVal);
                $this->body .= "{$bodyVal}\t ";
            }
            $this->body .= "\n";
        }
    }
     
    /**
     * 下载excel文件
     */
    public function downLoad($filename=''){
        if(!$filename)
            $filename = date('YmdHis',time()).'.xls';
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=$filename"); 
        header("Content-Type:charset=gb2312");
        if($this->head)
            echo $this->head;
        echo $this->body;
    }
     
    /**
     * 编码转换
     * @param type $string
     * @return string
     */
    public function charset($string){
        return iconv("utf-8", "gb2312", $string);
    }
}
?>