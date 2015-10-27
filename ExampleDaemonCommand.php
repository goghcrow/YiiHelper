<?php
/**
 *
 *
 * User: xiaofeng
 * Date: 2015/10/16
 * Time: 14:52
 */
class FetchWaybillDaemonCommand extends DaemonConsoleCommand
{
    /**
     * Yii init
     * 在主进程退出之前，主循环开始之前初始化数据
     * @author xiaofeng
     */
    public function init() {
        $this->_options = [
            'maxTimes'      => 0, // 最大运行次数，超过重启
            'maxMemory'     => 0, // 最大内存占用，超过重启
            'limitMemory'   => -1,// 内存限制
            'log'           => '/tmp/console/runtime/meicai_fetchWaybill.log', // log文件路径
            'pid'           => '/tmp/console/runtime/meicai_fetchWaybill.pid', // pid文件路径
            'help'          => "Usage:\n\n  填写usage  start|stop|restart|status|help\n\n",
        ];
    }

    /**
     * yii run & daemon run（Handle commands from cli）
     * @param array $args 接收控制台参数
     *
     * @author xiaofeng
     */
    public function run($args)
    {
        // 初始化参数数据
        // ......

        parent::run($args);
    }

    /**
     * Daemon main loop
     * @author xiaofeng
     */
    public function main()
    {
        // 业务逻辑
        // main会不断被调用，自行执行休眠
    }
}
