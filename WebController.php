<?php
require_once 'YiiHelper.php';

/**
 * Yii web��������������
 * Class WebController
 *
 * @author xiaofeng
 */
class WebController extends CController {

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


    public function getAuthZip() {
        $roleGroup = [
            '����Ա' => ['��̨����', '�û�����', '�ٵݹ���', '�������', '�˵�����'],
            '��̨Ӫ��' => ['�û�����', '�ٵݹ���', '�������', '�˵�����'],
        ];

        list($access, $roles) = self::accessRoles(__DIR__ . '/../controllers', $roleGroup, function($controllerId, $actionId) {
            return !in_array(strtolower($controllerId), ['config', 'debug'], true);
        });

        $access_php = "<?php\nreturn " . var_export($access, true) . ';';
        $roles_php = "<?php\nreturn " . var_export($roles, true) . ';';
        $pathPrefix = 'mxs' . DIRECTORY_SEPARATOR;

        __::sendZip('auth.zip', [
            $pathPrefix . 'access.php' => $access_php,
            $pathPrefix . 'roles.php' => $roles_php,
        ]);
    }

    private static function groupRoles($roleGroup) {
        $groupRoles = [];
        foreach($roleGroup as $role => $groups) {
            foreach($groups as $group) {
                if(!isset($groupRoles[$group])) {
                    $groupRoles[$group] = [];
                }
                // SET
                $groupRoles[$group][$role] = null;
            }
        }
        foreach($groupRoles as $group => &$gRoles) {
            $gRoles = array_keys($gRoles);
        }
        unset($gRoles);
        return $groupRoles;
    }

    private static function accessRoles($dirs, array $roleGroup, callable $filter = null) {
        $access = [];
        $roles = [];
        $groupRoles = self::groupRoles($roleGroup);
        foreach(YiiHelper::authGenerator($dirs, $filter) as $url => $info) {
            $access[$url] = $info;
            $group = $info['group'];
            if($group && isset($groupRoles[$group])) {
                $gRoles = $groupRoles[$group];
                foreach($gRoles as $role) {
                    if (!isset($roles[$role])) {
                        $roles[$role] = [];
                    }
                    $roles[$role][] = $url;
                }
            } else {
                // $roles['δ����'][] = $url;
            }
        }
        return [$access, $roles];
    }

}