<?php
require_once 'YiiHelper.php';

/**
 * Yii web控制器基类例子
 * Class WebController
 *
 * @author xiaofeng
 */
class WebController extends CController {

    /* @var string 控制器id*/
    protected $controllerId;

    /* @var string actionid*/
    protected $actionId;

    protected $defaultControllerId = 'home';

    /**
     * 获取子类控制器名称hacker
     * @author xiaofeng
     */
    public static function getControllerName()
    {
        return lcfirst(substr(static::class, 0, -strlen('Controller')));
    }

    public function __construct()
    {
        parent::__construct(self::getControllerName());
    }

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


    public function getAuthZip() {
        $roleGroup = [
            '管理员' => ['后台设置', '用户管理', '速递管理', '网点管理', '运单管理'],
            '后台营运' => ['用户管理', '速递管理', '网点管理', '运单管理'],
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
                // $roles['未分组'][] = $url;
            }
        }
        return [$access, $roles];
    }

}
