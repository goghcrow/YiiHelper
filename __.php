<?php

/**
 * Utils
 * Class _
 *
 * @author xiaofeng
 */
class __ {

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     *
     * @author xiaofeng
     */
    public static function strStartWith($haystack, $needle) /*:bool*/{
        if($needle === "") {
            return true;
        }
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     *
     * @author xiaofeng
     */
    public static function strEndWith($haystack, $needle) /*:bool*/{
        if($needle === "") {
            return true;
        }
        return (substr($haystack, -strlen($needle)) === $needle);
    }

    /**
     * 标量生成Generator
     * @param mixed $val
     * @return Generator
     *
     * @author xiaofeng
     */
    public static function var2Generator($val) {
        yield $val;
    }

    /**
     * 克隆一个返回值为一维结构的Generator
     * 此方法使Generator丧失原本Generator的优点~ 不得已不使用
     * @param Generator $gen
     * @return array
     *
     * @author xiaofeng
     */
    public static function cloneOneDimensionGenerator(Generator $gen) {
        $_ = [];
        foreach($gen as $val) {
            $_[] = $val;
        }
        $gen1 = function() use($_) {
            foreach($_ as $val) {
                yield $val;
            }
        };
        $gen2 = function() use($_) {
            foreach($_ as $val) {
                yield $val;
            }
        };

        return [$gen1(), $gen2()];
    }

    /**
     * 创建多文件夹递归遍历迭代器
     * @param string|array $dirs 文件夹路径或路径数组
     * @param callable|null $filter 文件迭代器过滤 参数签名
     *        filter(SplFileInfo $current, $key, RecursiveIterator $iterator) : bool
     * @return AppendIterator 返回迭代器 内部对象为 SplFileInfo
     * @throws UnexpectedValueException
     *
     * @author xiaofeng
     */
    public static function fileIterator($dirs, callable $filter = null) /*:AppendIterator*/ {
        if(!is_array($dirs)) {
            $dirs = [$dirs];
        }
        $multiIt = new AppendIterator();
        foreach($dirs as $dir) {
            $dirIt = new RecursiveDirectoryIterator($dir);
            $filterIt = $filter === null ? $dirIt : new RecursiveCallbackFilterIterator($dirIt, $filter);
            $multiIt->append(new RecursiveIteratorIterator($filterIt));
        }
        return $multiIt;
    }

    /**
     * 发送到客户端zip打包文件
     * @param string $downFileName 下载文件名
     * @param array $pathContent [path1=>content1, path2=>content2, ...]
     * @return bool
     *
     * @author xiaofeng
     */
    public static function sendZip($downFileName, array $pathContent) /*:bool*/ {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');
        $zip = new ZipArchive;
        $res = $zip->open($tmpFile, ZipArchive::CREATE);
        if(!$res) {
            return false;
        }
        foreach($pathContent as $path => $content) {
            $zip->addFromString($path, $content);
        }
        $zip->close();
        $fileContent = file_get_contents($tmpFile);
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . $downFileName . '"');
        header("Content-Length: " . mb_strlen($fileContent));
        echo $fileContent;
        return true;
    }
}
