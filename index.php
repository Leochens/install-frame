<?php 
/*
安装程序
 */

header("Content-Type:text/html;charset=utf-8");

//检测是否安装
if(file_exists('./install.lock'))
{
    die("你已经安装过了");
}

//同意协议
if(!isset($_GET['c'])||$_GET['c']=='agreement'){
    require './agreement.html';
}

//检测环境
if($_GET['c']=='test'){
    require './test.html';
}

//创建数据库

if($_GET['c']=='create'){
    require './create.html';
}

//安装成功
if($_GET['c']=='success'){
    //上一步的数据库配置工作现在来做
    
    //判断是否是post
    if($_SERVER['REQUEST_METHOD']=='POST')
    {
        $data = $_POST;
        //开始连接数据库
        
        $con =new mysqli("{$data['db_host']}:{$data['db_port']}",$data['db_user'],$data['db_pwd']);
        //如果有错误就报错
        $error = $con->connect_error;
        if(!is_null($error)){

            die("<script>alert('数据库连接失败');</script>");
        }
        //设置字符集
        $con->query("SET NAMES 'utf8'");
        $con->server_info>5.0 or die("<script>alert('请将您的mysql升级到5.0以上');history.go(-1)</script>");

        //创建数据库并选中
        
        if(!$con->select_db($data['db_name']))
        {
            $sql = 'CREATE DATABASE IF NOT EXISTS '.$data['db_name'].' DEFAULT CHARACTER SET utf8';
            $con->query($sql) or die('数据库创建失败');
            $con->select_db($data['db_name']);
        }

        //sql file
        $sql_file = file_get_contents('./blog.sql');
        $sql_array =preg_split("/;[\r\n]+/", 
        str_replace('zhl_', $data['db_prefix'], $sql_file));
        foreach ($sql_array as $k => $v) {
            if(!empty($v))
            {
                $con->query($v);
            }
        }
        $con->close();
        $db_conf=<<<php
<?php
return array(

//*************************************数据库设置*************************************
    'DB_TYPE'               =>  'mysqli',                 // 数据库类型
    'DB_HOST'               =>  '{$data['db_host']}',     // 服务器地址
    'DB_NAME'               =>  '{$data['db_name']}',     // 数据库名
    'DB_USER'               =>  '{$data['db_user']}',     // 用户名
    'DB_PWD'                =>  '{$data['db_pwd']}',      // 密码
    'DB_PORT'               =>  '{$data['db_port']}',     // 端口
    'DB_PREFIX'             =>  '{$data['db_prefix']}',   // 数据库表前缀
);
php;
    file_put_contents('./db.php',$db_conf);//填充文件
    touch('./install.lock');//创建一个锁文件

    require './success.html';
    }

}


 ?>