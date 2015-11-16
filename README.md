# YiiHelper
Yii1.x 注释提取，权限文档生成，api文档生成等相关代码

### PhpStorm 配置文档注释添加action权限信息
File->settings->Editor->File and Code Templates
Includes->PHP Function Doc Comment
Extension: php
~~~
/**
${PARAM_DOC}
#if (${TYPE_HINT} != "void") * @return ${TYPE_HINT}
#end
${THROWS_DOC}
*
#set($func = ${NAME})
#if ($func.contains("action"))
*
* @alias
* @description
* @group 未分组
*
#end
* @author author@meicai.cn
*/
~~~

### PhpStorm 配置文档注释添加controllerApi文档信息
File->settings->Editor->File and Code Templates
Includes->PHP Class Doc Comment
Extension: php
/**
 * Class ${NAME}
#if (${NAMESPACE}) * @package ${NAMESPACE}
#end
 *
#set($func = ${NAME})
#if ($func.contains("Controller"))
 *
 * @doc [Controller 说明]
 * @url [Api地址]
 *
#end
 * @author author@meicai.cn
 */
