<?php
//命令行脚本注册
return [
    // 指令定义
    'commands' => [
        \app\command\TestCommand::class,
        \app\command\LiveCheckCommand::class,
    ],
];
