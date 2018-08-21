SwaggerLumenYaml
==========

Swagger 2.0 for Lumen 5

对 [SwaggerLume](https://github.com/DarkaOnLine/SwaggerLume) 进行封装。
使其支持YAML文档。

安装
============

````
composer require --dev ptx/lumenyaml-swagger
````


#### `bootstrap/app.php`

- 去掉门面注释:
    ```php
         $app->withFacades();
    ```
- 添加配置加载:
    ```php
         $app->configure('swagger-lume');
    ```
- 注册服务:
    ```php
        $app->register(\PtxDev\Swagger\SwaggerLumenServiceProvider::class);
    ```
- 配置YAML目录 `config/swagger-lume.php`：
    ````php
    [
        "paths" => [
            "yamlAnnotations" => [base_path('docs')] // 默认目录
        ]
    ]
    ````

#### 其它使用见 [SwaggerLume](https://github.com/DarkaOnLine/SwaggerLume) 文档
