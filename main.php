<?php

date_default_timezone_set('UTC');
require_once('JSON.php');
require_once('ChatWorkApi.php');
require_once('ChatWorkTasksFormatter.php');

try{
    $chat_work_api = new ChatWorkApi($argv[1]);
    $chat_work_tasks = $chat_work_api->get('/v1/my/tasks?status=open');
    $myself_data = $chat_work_api->get('/v1/me');

    $chat_work_tasks_fomatter = new ChatWorkTasksFormatter($chat_work_tasks);
    $chat_work_formated_messages = $chat_work_tasks_fomatter->getFormatMessage();

    array_walk($chat_work_formated_messages,
            array($chat_work_api, 'sendMessage'), $myself_data['room_id']);
}catch(Exception $e){
    echo $e->getMessage();
}
