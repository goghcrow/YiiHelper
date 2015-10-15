<?php
/**
 *
 *
 * User: xiaofeng
 * Date: 2015/9/8
 * Time: 13:20
 * @var array $api
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>美鲜送API</title>
    <style>
        body { margin: 0; font:14px/20px Verdana, Arial, sans-serif; color: #333; background: #f8f8f8;}
        h1, h2, pre { margin: 0; padding: 0;}
        /*h1 { font:bold 28px Verdana,Arial; background:#8892BF; padding: 12px 5px; border-color: #4F5B93; border-bottom: 4px solid #669; box-shadow: 0 1px 4px #bbb; color: #222;}*/
        h1 { font:bold 24px "Microsoft YaHei", Verdana, Arial; background:#8892BF; padding: 12px 5px; border-color: #4F5B93; border-bottom: 4px solid #669; box-shadow: 0 .25em .25em rgba(0,0,0,.1); color: #222;}
        h2 { font:normal 20px/22px "Microsoft YaHei", Georgia, Times, "Times New Roman", serif; padding: 5px 0 8px; margin: 20px 10px 0; border-bottom: 1px solid #ddd; cursor:pointer; color:#369;}
        p, dd { color: #555; }
        .api-block { margin-left: 40px;}
        .module-block { margin-bottom: 15px; }
        h2 u { font-size:20px;text-decoration:none;padding:10px; }
        pre {font: normal 15px "Fira Mono", "Source Code Pro", monospace, "Microsoft YaHei"; background-color: #fff;    box-shadow: inset 0 0 0 1px rgba(0,0,0,.15);
            border-radius: 0 0 2px 2px;padding: 15px; margin-top: 20px;}
        a {text-decoration: none; color:#222; }
    </style>
    <script>
        // @see https://github.com/laruence/yar/blob/bfc6906b454ad75778ba3b13f43cf4af8c54e1bf/yar_server.c
        function _t(elem) {
            var block = elem.nextSibling;
            var info = elem.getElementsByTagName('u')[0];
            while (block) {
                if ( block.nodeType == 1 && block.className.indexOf('api-block') > -1 ) {
                    break;
                }
                block = block.nextSibling;
            }
            var isHidden = block.style.display == 'none';
            block.style.display = isHidden ? '' : 'none';
            info.innerHTML = isHidden ? '-'  : '+';
        }
    </script>
</head>
<body>
<?php foreach($api as $controllerName => $controller):?>
    <div class="module-block">
        <h1><a href="<?php echo "/" . $controller['url'] . "index"?>"><?php echo $controller['doc'] . '&nbsp;&nbsp;' . $controller['url']?></a></h1>
        <?php foreach($controller['actions'] as $actionName => $doc):?>
            <h2 onclick="_t(this)"><u>+</u><?php echo /*lcfirst($controllerName) . '/' .*/ lcfirst($actionName)?></h2>
            <div class="api-block" style="display:none">
            <pre style="white-space:pre-line">
                <?php echo $doc?>
            </pre>
            </div>
        <?php endforeach;?>
    </div>
<?php endforeach;?>
</body>
</html>
