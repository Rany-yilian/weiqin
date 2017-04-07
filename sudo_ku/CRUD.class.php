<?php

class CRUD{

      public static   $table_sudo_ku ="sudo_ku";      public static   $table_sudo_ku_sn ="sudo_ku_sn";
      public static   $table_sudo_ku_user ="sudo_ku_user";
	  public static   $table_sudo_ku_share ="sudo_ku_share";
      public static   $table_sudo_ku_user_record ="sudo_ku_user_record";
      public static   $table_sudo_ku_user_award ="sudo_ku_user_award";
	  public static   $table_sudo_ku_prize ="sudo_ku_prize";	
      public static   $table_uni_account_modules="uni_account_modules";
	  
      public static   function findById($table,$id){
          return pdo_fetch("select * from ".tablename($table)." where id=:id",array(':id'=>$id)) ;
      }
	  public static   function findByIdsn($table,$sn){		 return pdo_fetch("select * from ".tablename($table)." where sn=:sn",array(':sn'=>$sn)) ;	  }
      public static   function findPrizeByJid($table,$jid){

		 return pdo_fetchall("select * from ".tablename($table)." where jid=:jid order by location asc",array(':jid'=>$jid)) ;

	  }
	  
      public static function findCount($table,$params=array()){
          if(!empty($params)){
              $where=" where ";
              $index=0;
              foreach($params as $key =>$value){
                  $where.=substr($key,1)."=".$key." ";
                  if($index<count($params)-1){
                      $where.=" and ";
                  }
                  $index++;
              }
          }
          return pdo_fetchcolumn("select COUNT(*) from ".tablename($table).$where,$params);
      }
	  
	  public static function findDayCount($table,$params=array()){
		  
          if(!empty($params)){
              $where=" where ";
              $index=0;
              foreach($params as $key =>$value){
                  $where.=substr($key,1)."=".$key." ";
                  if($index<count($params)-1){
                      $where.=" and ";
                  }
                  $index++;
              }
          }
		  $day_start = strtotime(date('Y-m-d',time()).' 00:00:00');
		  $day_end = strtotime(date('Y-m-d',time()).' 23:59:59');
		  $where .=" and createtime>".$day_start." and createtime<".$day_end;
		 
          return pdo_fetchcolumn("select COUNT(*) from ".tablename($table).$where,$params);
      }
	  
      public static function  findList($table,$params=array()){
          if(!empty($params)){
              $where=" where ";
              $index=0;
              foreach($params as $key =>$value){
                  $where.=substr($key,1)."=".$key." ";
                  if($index<count($params)-1){
                      $where.=" and ";
                  }
                  $index++;
              }
          }
		  
          return pdo_fetchall("select * from ".tablename($table).$where,$params);
      }
      
      
       public static function  findListEx($table,$fileds,$params=array()){
          if(!empty($params)){
              $where=" where ";
              $index=0;
              foreach($params as $key =>$value){
                  $where.=substr($key,1)."=".$key." ";
                  if($index<count($params)-1){
                      $where.=" and ";
                  }
                  $index++;
              }
          }
		 
          return pdo_fetchall("select ".$fileds." from ".tablename($table).$where,$params);
      }
	  
      public  static  function  create($table,$data=array()){
         return  pdo_insert($table,$data);
      }

      public  static  function  update($table,$data = array(), $params = array()){
          return pdo_update($table,$data,$params);
      }

      public  static  function  updateById($table,$data = array(),$id){
          return pdo_update($table,$data,array('id'=>$id));
      }



      public  static  function  deleteByid($table,$id){
          return   pdo_delete($table,array('id'=>$id));
      }

      public  static  function  delete($table,$params = array()){
          return   pdo_delete($table,$params);
      }

}