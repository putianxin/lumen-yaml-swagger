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
    
#### `app/Providers/AppServiceProviders.php`   
- 添加默认@SWG/Info
    ```php
      /**
       * @SWG\Swagger(
       *   @SWG\Info(
       *     title="My first swagger documented API",
       *     version="1.0.0"
       *   )
       * )
       */
    ```
#### 其它使用见 [SwaggerLume](https://github.com/DarkaOnLine/SwaggerLume) 文档
