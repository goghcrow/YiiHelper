<?php
require_once '__.php';

/**
 * Class YiiHelper
 *  1. 依赖Yii的autoload
 *  2. api文档展示需要 urlManager 中的controller类与pattern一致
 * @author xiaofeng
 */
class YiiHelper {

    const CONTROLLER_FILE_POSTFIX = 'Controller.php';


    /**
     * 多个路径下控制器权限列表 Generator
     * @param string|array $dirs
     * @return Generator
     *
     * @author xiaofeng
     */
    public static function authGenerator($dirs, callable $filter = null) /*:Generator*/ {
        $proxyGenerator = function() use($dirs, $filter) {
            $controllerIdGenerator = self::controllerIdGenerator($dirs);
            foreach($controllerIdGenerator as $controllerId) {
                foreach(self::actionDocumentGenerator($controllerId, $filter) as list($controllerId, $actionId, $doc)) {
                    $url = "/" . lcfirst($controllerId) . "/". lcfirst($actionId);
                    yield $url => $doc;
                }
            }
        };
        return self::atItemGenerator($proxyGenerator(), ['alias', 'description', 'group']);
    }

    /**
     * 获取多路径下api文档
     * @param Generator $controllerIdGenerator
     * @param callable|null $filter 函数签名 filter(string $controllerId) : bool
     *
     * @author xiaofeng
     */
    public static function apiInfo(Generator $controllerIdGenerator, callable $filter = null) /* :array*/ {
        $api = [];

        // Generator 不支持clone！！！
        list($idsGen1, $idsGen2) = __::cloneOneDimensionGenerator($controllerIdGenerator);

        // 添加doc，url
        $controllersDocGenerator = YiiHelper::controllersDocumentGenerator($idsGen1, $filter);
        $atGen = YiiHelper::atItemGenerator($controllersDocGenerator, ['doc', 'url']);
        foreach($atGen as $controllerId => $info) {
            $api[$controllerId] = $info;
        }

        // 添加action原始文档
        foreach($idsGen2 as $controllerId) {
            $actionDocGen = YiiHelper::actionDocumentGenerator($controllerId, $filter);
            foreach($actionDocGen as list($controllerId, $actionId, $doc)) {
                $api[$controllerId]['actions'][$actionId] = $doc;
            }
        }
        return $api;
    }

    /**
     * 获取 Yii action 内@开头特定字段文档的 Generator
     * @param Generator $documentGenerator [/controllerId/actionId => doc]
     * @param array $atNames
     * @return Generator
     *
     * @author xiaofeng
     */
    public static function atItemGenerator(Generator $documentGenerator, array $atNames) /*:Generator*/ {
        foreach($documentGenerator as $key => $document) {
            $info = [];
            foreach($atNames as $atName) {
                preg_match('/@' . $atName . '(.*)/', $document, $matches);
                $info[$atName] = isset($matches[1]) ? trim($matches[1]) : '';
            }
            yield $key => $info;
        }
    }

    /**
     * 某控制器actions文档Generator
     * @param $controllerId
     * @param callable|null $filter 函数签名 filter(string $controllerId, string $actionId) : bool
     * @return Generator [controllerId, actionId, doc]
     *
     * @author xiaofeng
     */
    public static function actionDocumentGenerator($controllerId, callable $filter = null) {
        foreach(self::actionGenerator($controllerId) as $action) {
            /* @var $action ReflectionMethod */
            $actionId =lcfirst(substr($action->getName(), strlen('action')));
            // filter
            if($filter !== null) {
                if($filter($controllerId, $actionId) === false) {
                    continue;
                }
            }
            $doc = $action->getDocComment() or '';
            yield [$controllerId, $actionId, $doc];
        }
    }

    /**
     * 多控制器文档注释Generator
     * @param Generator $controllerIdGenerator
     * @param callable|null $filter 函数签名 filter(string $controllerId) : bool
     *
     * @author xiaofeng
     */
    public static function controllersDocumentGenerator(Generator $controllerIdGenerator, callable $filter = null) /*:Generator*/ {
        foreach($controllerIdGenerator as $controllerId) {
            // Yii 1.x controller规则
            // 文件命名与类名均以Controller.php结尾
            $class = ucfirst($controllerId) . 'Controller';
            if($filter !== null) {
                if($filter($controllerId) === false) {
                    continue;
                }
            }
            $controller = new ReflectionClass($class);
            $controllerDocument = $controller->getDocComment() or '';
            yield $controllerId => $controllerDocument;
        }
    }

    /**
     * 单控制器内actions反射方法Generator
     * @param $controllerId
     * @return Generator [ReflectionMethod]
     * @throws ReflectionException
     *
     * @author xiaofeng
     */
    public static function actionGenerator($controllerId) /*:Generator*/ {
        // Yii 1.x controller规则
        // 文件命名与类名均以Controller.php结尾
        $class = ucfirst($controllerId) . 'Controller';
        $controller = new ReflectionClass($class);

        // 继承Yii1.x框架控制器基类
        if($controller->isSubclassOf('CController')) {
            // public非static方法
            $methods = $controller->getMethods(ReflectionMethod::IS_PUBLIC ^ ReflectionMethod::IS_STATIC);
            // 过滤action
            foreach($methods as $method) {
                $methodName = $method->getName();
                $char = ord(substr($methodName, strlen('action'), 1));

                $isAction = $method->class === $class               // 非继承方法
                    && __::strStartWith($methodName, 'action')       // 方法名称以action打头
                    && ($char > 64 && $char < 91);                  // action后首字母大写
                if($isAction) {
                    yield $method;
                }
            }
        }
    }

    /**
     * 控制器id Generator
     * @param string|array $dirs
     * @return Generator [controllerId]
     *
     * @author xiaofeng
     */
    public static function controllerIdGenerator($dirs) {
        return self::controllerGenerator($dirs, function($file) {
            /* @var SplFileInfo $file */
            $fileName = $file->getFilename();
            return substr($fileName, 0, strlen($fileName) - strlen(self::CONTROLLER_FILE_POSTFIX));
        });
    }

    /**
     * 根据callable创建一个特定的控制器Generator
     * @param $dirs
     * @param callable $callback 函数签名 callable(SplFileInfo $file)
     * @return Generator
     *
     * @author xiaofeng
     */
    public static function controllerGenerator($dirs, callable $callback) /*:Generator*/ {
        foreach(self::controllerFileIterator($dirs) as $fileIt) {
            yield $callback($fileIt);
        }
    }

    /**
     * 创建yii controller文件迭代器
     * @param string|array $dirs
     * @return AppendIterator [SplFileInfo]
     *
     * @author xiaofeng
     */
    public static function controllerFileIterator($dirs) /*:AppendIterator*/ {
        return __::fileIterator($dirs,
            function(SplFileInfo $current, $_, RecursiveIterator $iterator) {
                if($iterator->hasChildren()) {
                    return true;
                }
                $fileName = $current->getFilename();
                $isControllerFile = __::strEndWith($fileName, self::CONTROLLER_FILE_POSTFIX);
                return $isControllerFile;
            });
    }
}
