#CLPHP

A simple PHP Classes lib package.

*Version: 1.0.0*

*Author: wallax*

*Email: wallax@126.com*

If you can't read this file, please [Google](https://translate.google.com)!

###功能模块

* 参数配置
* 常用功能
* 网络模块
* 模板模块
* 日志模块
* PDO模块
* 其他扩展

##使用方法

直接引用主文件：

```php
require "clphp.php";
```

*日后会提供若干实例代码*

#####判断模块正确性

```php
require "clphp.php";

if(!defined("PHPFLAG")){
    die('引用非法');
}
```

#####导入自定义模块

本框架可以自动导入开发者自定义的模块，只需把想要引用的模块放到主文件下lib的文件夹内，程序就会自行应用自定义库

*开发者可以修改主文件内的地址*

##配置模块

正式使用前，应当先根据实际情况对环境进行设定

用户可以直接修改库文件内的配置信息，

或者调用修改配置的方法**（推荐）**

例如：
```php
//设定数据库配置信息
CONFIG::setdb(
    //数据库服务器
    "127.0.0.1",
    //数据库名称
    "test",
    //用户名
    "root",
    //密码
    "123");
```

设定验证码配置：

```php

//设定验证码生成器信息
CONFIG::setChkCode(
    //字符表，默认abcdefghijklmnopqrstuvwxyz0123456789
    "abcdefghijklmnopqrstuvwxyz0123456789",
    //生成的字体文件，默认为同级目录下的font.ttf，压缩包内含有示例
    "font.ttf",
    //生成之后记录的session键名，默认chkimg
    "chkimg");
```

##网络模块

提供了两个简单的模拟方法：**get** 和 **post**

###get

参数：

* *地址*

返回值：

* *读取到的数据*

例如：

```php
echo WEB::get("http://example.com");
```

###post

参数：

* *地址*
* *要发送的数据，键值对数组*

返回值：

* *读取到的数据*

例如：

```php
echo WEB::post("http://example.com", array("name" => "sb"));
```

##日志模块

提供简单常用的日志记录功能

####设置日志文件

设置日志工作的文件，如果文件不存在则会创建新文件并初始化日志

例如：

```php
LOG::setfile("test.log")
```

####日志类型

日志记录时使用种类型，分别对应各种不同的情况

* ERROR  *错误，必须中断的异常*
* WARN   *警告，可以正常运行，但会出现不可预料的结果*
* NORMAL *普通，程序正常运行时的技术信息*
* NOTICE *提示，其他应该记录的信息，如统计*

####记录方法

**set**

参数：

* *类型，上述的四种类型*
* *代码，异常代码*
* *提示信息*

返回结果：

* *成功返回true，失败返回false*

例如：

```php
//设定日志文件
LOG::setfile("test.log");

//记录错误信息
LOG::set(LOG::ERROR, 1001, "Error test");

//记录警告信息
LOG::set(LOG::WARN, 1002, "WARN test");

//记录普通信息
LOG::set(LOG::NORMAL, 1003, "NORMAL test");

//记录提示信息
LOG::set(LOG::NOTICE, 1004, "NOTICE test");
```

##常用方法

UTIL类提供了几个常用的方法

####getip()

获取访问者ip地址

参数： 无

返回值：

* *字符串，ip地址*

```php
echo UTIL::getip();
```

####getiparea($ip)

获取ip地址的归属地，信息由淘宝地址库提供

参数：
* *字符串，ip地址，必须*

返回值：
* *对象，ip地址信息*

```php
print_r(UTIL::getiparea());
```

####crypto($str)

简单将字符串加密，base64+sha1

参数：
* *字符串*

返回值：
* *加密后的字符串*

```php
echo UTIL::crypto("hello php");
```

####getext($fn)

获取文件的扩展名

参数：
* *字符串，文件名，必须*

返回值：
* *字符串，文件扩展名*

```php
echo UTIL::getext("test.jpg");
```

####getmdfile($fn)

获取文件的md5加密后的文件名

参数：
* *字符串，文件名，必须*

返回值：
* *字符串，文件名*

例如：
```php
echo UTIL::getmdfile("test.htm");
```

返回值例如：

*202cb962ac59075b964b07152d234b70.htm*

####gotourl($url)

跳转页面

参数：
* *字符串，页面地址，必须*

返回值：无

**注意！调用此函数前不能输入任何信息**

```php
UTIL::gotourl("../about.htm");
```

####genchkimg($len)

生成验证码，并且记录session

参数：
* *整数，验证码长度*

返回值：无

该方法会修改content-type，所以在这之前不能输出任何信息，

此外，该方法还会记录session，加密方式为验证码小写的md5值

```php
UTIL::genchkimg(5);
```

##模板

简易的模板功能，实现前后端分离开发

####创建一个模板

一个简单的html模板：

```html
<!doctype html>
<html>
<head>
    <title>{$title}</title>
    <meta charset="utf-8">
</head>
<body>
   <!-- 简单格式 -->
   <div class="{$class}">{$div}</div>
   
   <!-- 复杂格式 -->
   {$article{<div>
        <b>{$article.title}</b>
        <span>{$article.date}</span>
    </div>}}
</body>
</html>
```

####创建实例

参数：

* *模板文件*

返回值：

* *模板实例*

```php
$tmp = new template("index.htm");
```

####替换模板内容

参数：

* *字符串，键名*
* *字符串或数组，值*

返回值：无

例如：

```php
$tmp -> set("title", "标题");

$tmp -> set("class", "haha");

$tmp -> set("div", "this is div");

$tmp -> set("article", array(
    //子项
    "title" => "这是标题", 
    "date" => "这是日期"));
```

####输出模板

修改完模板之后要输出才能显示

参数：无

返回值：无

```php
$tmp -> put();
```

##PDO模块

封装了pdo的常用方法，简化了操作

**使用前先配置好数据库信息，详情见上文**

####查询

**FPDO::select($sql, $param)**

参数：

* *SQL查询语句*
* *预处理参数*

返回值：

* *查询结果集*

```php
$res = FPDO::select("select username from user where id=:id and isuse=:iu", array(
    ":id" => 1,
    ":iu" => 1));
    
print_r($res);
```

####插入

**FPDO::insert($table, $values)**

参数：

* *表名*
* *数组，字段和值*

返回值：

* *影响的行数*

```php
$res = FPDO::insert("user", array(
    "username" => "admin",
    "isuse" => 1));
    
echo $res;
```

####获取最后插入的id

**FPDO::lastins()**

参数：无

返回值：

* *最后插入的id*

```php
$res = FPDO::lastins();
    
echo $res;
```

####执行SQL语句

**FPDO::exec($sql, $param)**

参数：

* *SQL语句*
* *预处理参数*

返回值：

* *影响的行数*

```php
$res = FPDO::exec("delete from user where username=:u", array(
    ":u" => "xiaoye");
    
echo $res;

$res = FPDO::exec("update user set username=:u where id=:id", array(
    ":u" => "admin",
    ":id" => 1));
```

####分页查询

**FPDO::selectpage($sql, $param)**

参数：

* *SQL查询语句*
* *数组，分页信息，第一个参数为页数，第二个为每页多少行*
* *预处理参数*

返回值：

* *查询结果集*

```php
$res = FPDO::selectpage(
    "select username from user where id=:id and isuse=:iu",
    //第1页，每页10个
    array(1, 10),
    array(
    ":id" => 1,
    ":iu" => 1));
    
print_r($res);
```

####查询行数

返回该条件下的行数，用于查询是否存在记录

**FPDO::getrows($table, $where, $param)**

参数：

* *表名*
* *条件*
* *预处理参数*

返回值：

* *行数*

```php
$res = FPDO::getrows("select username from user where id=:id and isuse=:iu", array(
    ":id" => 1,
    ":iu" => 1));
    
echo $res;
```

####查询键值对的值

使用于key-value的数据表

**FPDO::getkey($key, $table[, $keyname] [, $valuename])**

参数：

* *键名*
* *表名*
* *数据库键的字段名，默认为name*
* *数据库值的字段名，默认为content*

返回值：

* *查询结果值*

```php
$res = FPDO::getkey("title","baseinfo");
    
echo $res;
```

##问题反馈

本框架匆忙上线，未经大量测试，如果发现bug，请联系[wallax@126.com](mailto://wallax@126.com)

有什么新功能建议也可以联系本人。