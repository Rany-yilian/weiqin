#!/bin/bash
for((i=1;$i<60;i++));do
	sleep 1
	curl "http://www.gege.cn/autoApi.php";
done