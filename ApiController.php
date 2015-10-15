<?php
require_once 'YiiHelper.php';

/**
 * Yii api��������������
 * Class ApiController
 *
 * @author xiaofeng
 */
class ApiController extends CController {

    /* @var string ������id*/
    protected $controllerId;

    /* @var string actionid*/
    protected $actionId;

    protected $defaultControllerId = 'home';
    protected $defaultActionId = 'index';

    /**
     * ��ʼ��controllerId��actionId
     * @return $this
     *
     * @author xiaofeng
     */
    public function initControllerActionId() {
        $this->controllerId = Yii::app()->controller->id;
        $pageUrl = substr(Yii::app()->request->getUrl(), strlen(Yii::app()->request->baseurl));
        $urlParams = substr($pageUrl, 1);
        $paramArr = explode('/', $urlParams);

        // $this->defaultActionId;
        $defaultAction = Yii::app()->getController()->defaultAction;

        if(count($paramArr) > 1 && (int)($paramArr[1]) > 0){
            $this->actionId = $defaultAction;
        } else {
            if($urlParams && count($paramArr) > 1){
                $this->actionId = ($paramArr[1] == '') ? 'index' : preg_replace('/^(.*)\?(.*)$/', '$1', $paramArr[1]);
            } else {
                $this->actionId = $defaultAction;
            }
            if($this->controllerId == ''){
                $this->controllerId = $paramArr[0];
            }
        }

        // controllerID Ϊ x/y��ʽ�����
        if(count($paramArr) >= 3) {
            if($paramArr[0] == 'index.php') {
                $this->controllerId = $paramArr[1];
                $this->actionId = preg_replace('/^(.*)\?(.*)$/', '$1', $paramArr[2]);
            } else {
                $this->controllerId = $paramArr[0] . '/' . $paramArr[1];
                $this->actionId = preg_replace('/^(.*)\?(.*)$/', '$1', $paramArr[2]);
            }
        }

        $this->controllerId = $this->controllerId ?: $this->defaultControllerId;
        $this->actionId = $this->actionId ?: $defaultAction;
    }

    public function init() {
        $this->initControllerActionId();

        // ......
    }

    /**
     * Get ��ȡAPI �ĵ�
     * �ٶ����нӿڽ���ͨ��POST��ʽ���ʣ�������ʽ�������ĵ�
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
            // �ų�home������
            return !in_array(strtolower($controllerId), ['home'], true);
        });

        if($api) {
            // FIXME ��yii�� $this->renderPartial()������д
            Yii::app()->end(include __DIR__ . '/views/apidoc.php');
        } else {
            throw new CHttpException(403);
        }
    }

    /**
     * api�ĵ�Dispatcher
     * @param callable|null $filter
     * @return array
     *
     * @author xiaofeng
     */
    public function apiDcoumentDispatcher(callable $filter = null) {

        // ����Ĭ�Ͽ�����, ��ȡ����apicontroller�ļ��ĵ�
        if($this->controllerId === $this->defaultControllerId) {
            $controllerIdGenerator = YiiHelper::controllerIdGenerator(__DIR__ . '/../controllers');
            $api = YiiHelper::apiInfo($controllerIdGenerator, $filter);
        }

        // ���ʵ�ǰ������Ĭ�Ϸ�������ȡ��ǰapicontroller�ĵ�
        else if($this->actionId === $this->defaultActionId) {
            $controllerIdGenerator = __::var2Generator($this->controllerId);
            $api = YiiHelper::apiInfo($controllerIdGenerator, $filter);
        }

        // ���ؾ���controllerId/actionId�ĵ�
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