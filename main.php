<?php

date_default_timezone_set('UTC');
require_once('ChatWorkApi.php');
require_once('ChatWorkTasksFormatter.php');

try{
    $chat_work_api = new ChatWorkApi($argv[1]);
    $chat_work_tasks = $chat_work_api->get('/v1/my/tasks?status=open');
    $myself_data = $chat_work_api->get('/v1/me');

    $chat_work_tasks_fomatter = new ChatWorkTasksFormatter($chat_work_tasks);
    $chat_work_formated_messages = $chat_work_tasks_fomatter->getFormatMessage();
    var_dump($chat_work_formated_messages);

    foreach($chat_work_formated_messages as $message){
        $chat_work_api->sendMessage($myself_data['room_id'], $message);
    }
}catch(Exception $e){
    echo $e->getMessage();
}
