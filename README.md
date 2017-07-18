# tp5-baidutongji

感谢   https://github.com/mushan0703/BaiduTongji

主要是参考这个项目做了一点小的调整


>请参考百度统计文档使用,本项目可以配合tp5 + 使用
## 安装

1. 安装包文件

  ```shell
 composer require melodic/baidu-tongji
  ```

```php
<?php

namespace app\Index\Controllers;

use think\Controller;
use mcmf\BaiduTongji\BaiduTongji;

class Index extends Controller
{

    public function index()
    {
        $baiduTongji= new BaiduTongji();
        $today=date('Ymd');
        $yesterday=date('Ymd',strtotime('yesterday'));
        $result=$baiduTongji->getData([
            'method' => 'trend/time/a',
            'start_date' => $today,
            'end_date' => $today,
            'start_date2' => $yesterday,
            'end_date2' => $yesterday,
            'metrics' => 'pv_count,visitor_count',
            'max_results' => 0,
            'gran' => 'day',
        ]);
        return json($result);
    }
}
```


