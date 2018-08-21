<?php

namespace PtxDev\Swagger;

use Exception;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Swagger\Annotations\OpenApi;
use Swagger\Annotations\Swagger;
use Swagger\Util;
use SwaggerLume\SecurityDefinitions;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Generator extends \SwaggerLume\Generator
{
    public static function generateDocs()
    {
        $appDir = config('swagger-lume.paths.annotations');
        $docDir = config('swagger-lume.paths.docs');
        if (! File::exists($docDir) || is_writable($docDir)) {
            // delete all existing documentation
            if (File::exists($docDir)) {
                File::deleteDirectory($docDir);
            }

            self::defineConstants(config('swagger-lume.constants') ?: []);

            File::makeDirectory($docDir);
            $excludeDirs = config('swagger-lume.paths.excludes');
            if (version_compare(config('swagger-lume.swagger_version'), '3.0', '>=')
                && function_exists('\OpenApi\scan')) {
                $swagger = \OpenApi\scan($appDir, ['exclude' => $excludeDirs]);
            } else {
                $swagger = \Swagger\scan($appDir, ['exclude' => $excludeDirs]);
            }

            if (config('swagger-lume.paths.base') !== null) {
                $swagger->basePath = config('swagger-lume.paths.base');
            }

            $filename = $docDir.'/'.config('swagger-lume.paths.docs_json');

            // 兼容yaml
            self::loadYaml($filename, $swagger, $excludeDirs);

            // $swagger->saveAs($filename);

            if (strpos(app()->version(), '5.5.*') > -1) {
                // lumen5.5
                \SwaggerLume\Generator::appendSecurityDefinitions($filename);
            } else {
                // lumen5.6
                $security = new SecurityDefinitions();
                $security->generate($filename);
            }
        }
    }

    public static function getYamlData()
    {
        $excludeDirs = config('swagger-lume.paths.excludes');

        // 读取注释目录并解析，支持数组
        $yamlDirs = config('swagger-lume.paths.yamlAnnotations', base_path('apps'));
        $yamlData = [];
        if (is_string($yamlDirs)) {
            $yamlDirs = [$yamlDirs];
        }
        if (is_array($yamlDirs)) {
            foreach ($yamlDirs as $yamlDir) {
                $finder = self::finder($yamlDir, $excludeDirs);
                foreach ($finder as $file) {
                    try {
                        $fileData = Yaml::parse(file_get_contents($file));
                        $yamlData = self::mergeData($yamlData, $fileData);
                    } catch (\Exception $e) {
                        throw new Exception('Failed to parse file("' . $file . '"):' . $e->getMessage());
                    }
                }
            }
        }
        return $yamlData;
    }

    /**
     * @param                 $filename
     * @param Swagger|OpenApi $swagger
     * @throws Exception
     */
    private static function loadYaml($filename, $swagger)
    {
        $yamlData = self::getYamlData();

        // 迁移PHP解析出来的数据
        $phpData = (array) $swagger->jsonSerialize();

        // 保存文件
        self::saveAs($filename, json_encode(self::mergeData($phpData, $yamlData)));
    }

    private static function mergeData($oldData, $newData)
    {
        if (!empty($newData)) {
            $keys = array_keys($newData);
            foreach ($keys as $key) {
                // 该字段已存在，且为数组则合并数据；否则则覆盖原有数据
                if (isset($oldData[$key]) && is_array($newData[$key])) {
                    $oldData[$key] = array_merge((array) $oldData[$key], $newData[$key]);
                } else {
                    $oldData[$key] = $newData[$key];
                }
            }
        }
        return $oldData;
    }

    private static function saveAs($filename, $data)
    {
        if (file_put_contents($filename, $data) === false) {
            throw new Exception('Failed to saveAs("' . $filename . '")');
        }
    }

    protected static function defineConstants(array $constants)
    {
        if (! empty($constants)) {
            foreach ($constants as $key => $value) {
                defined($key) || define($key, $value);
            }
        }
    }

    private static function finder($directory, $exclude = null)
    {
        if ($directory instanceof Finder) {
            return $directory;
        } else {
            $finder = new Finder();
            $finder->sortByName();
        }
        $finder->files()->followLinks()->name('*.yaml');
        if (is_string($directory)) {
            if (is_file($directory)) { // Scan a single file?
                $finder->append([$directory]);
            } else { // Scan a directory
                $finder->in($directory);
            }
        } elseif (is_array($directory)) {
            foreach ($directory as $path) {
                if (is_file($path)) { // Scan a file?
                    $finder->append([$path]);
                } else {
                    $finder->in($path);
                }
            }
        } else {
            throw new InvalidArgumentException('Unexpected $directory value:' . gettype($directory));
        }
        if ($exclude !== null) {
            if (is_string($exclude)) {
                $finder->notPath(Util::getRelativePath($exclude, $directory));
            } elseif (is_array($exclude)) {
                foreach ($exclude as $path) {
                    $finder->notPath(Util::getRelativePath($path, $directory));
                }
            } else {
                throw new InvalidArgumentException('Unexpected $exclude value:' . gettype($exclude));
            }
        }
        return $finder;
    }
}
