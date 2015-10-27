<?php
require_once 'YiiHelper.php';

/**
 * Yii api控制器基类例子
 * Class ApiController
 *
 * @author xiaofeng
 */
class ApiController extends CController {

    /* @var string 控制器id*/
    protected $controllerId;

    /* @var string actionid*/
    protected $actionId;

    protected $defaultControllerId = 'home';
    protected $defaultActionId = 'index';

    /**
     * Example
     * 初始化controllerId与actionId
     * @return $this
     *
     * @author xiaofeng
     */
    public function initControllerActionId() {
        $this->actionId = Yii::app()->getController()->defaultAction;
        $this->controllerId = Yii::app()->controller->id;

        $pageUrl = substr(Yii::app()->request->getUrl(), strlen(Yii::app()->request->baseurl));
        $paramArr = explode('/', substr($pageUrl, 1));

        if(!empty($paramArr[1])){
            $this->actionId = preg_replace('/^(.*)\?(.*)$/', '$1', $paramArr[1]);
        }
    }


    public function init() {
        $this->initControllerActionId();

        // ......
    }

    /**
     * Get 获取API 文档
     * 假定所有接口仅能通过POST方式访问，其他方式均返回文档
     * @param string $actionID
     * @return CAction|CInlineAction
     * @throws CException
     * @author xiaofeng
     */
    public function createAction($actionID)
    {
        $isPost = isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'], 'POST');
        if(!defined('YII_DEBUG') || !YII_DEBUG || $isPost) {
            return parent::createAction($actionID);
        }

        $api = $this->apiDcoumentDispatcher(function($controllerId) {
            // 排除home控制器
            return !in_array(strtolower($controllerId), ['home'], true);
        });

        if($api) {
            // FIXME 用yii的 $this->renderPartial()方法重写
            Yii::app()->end(include __DIR__ . '/views/apidoc.php');
        } else {
            throw new CHttpException(403);
        }
    }

    /**
     * api文档Dispatcher
     * @param callable|null $filter
     * @return array
     *
     * @author xiaofeng
     */
    public function apiDcoumentDispatcher(callable $filter = null) {

        // 访问默认控制器, 获取所有apicontroller文件文档
        if($this->controllerId === $this->defaultControllerId) {
            $controllerIdGenerator = YiiHelper::controllerIdGenerator(__DIR__ . '/../controllers');
            $api = YiiHelper::apiInfo($controllerIdGenerator, $filter);
        }

        // 访问当前控制器默认方法，获取当前apicontroller文档
        else if($this->actionId === $this->defaultActionId) {
            $controllerIdGenerator = __::var2Generator($this->controllerId);
            $api = YiiHelper::apiInfo($controllerIdGenerator, $filter);
        }

        // 返回具体controllerId/actionId文档
        else {
            $controllerIdGenerator = __::var2Generator($this->controllerId);
            $api = YiiHelper::apiInfo($controllerIdGenerator, $filter);
            if(empty($api[$this->controllerId]["actions"][$this->actionId])) {
               return $api;
            }
            $actionDoc = $api[$this->controllerId]["actions"][$this->actionId];
            $api[$this->controllerId]["actions"] = [$this->actionId => $actionDoc];
        }

        return $api;
    }
}
