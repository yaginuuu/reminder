<?php

date_default_timezone_set('UTC');
require_once('JSON.php');
require_once('ChatWorkApi.php');
require_once('FormatTextToRemindForChatWork.php');

$today = time();
try{
    $chat_work_api = new ChatWorkApi($argv[1]);
    $chat_work_tasks = $chat_work_api->get('/v1/my/tasks?status=open');
    $myself_data = $chat_work_api->get('/v1/me');

    $format_text_to_remind = new FormatTextToRemindForChatWork($chat_work_tasks);
    $formatted_text_messages = $format_text_to_remind->getFormatMessageText();
    $formatted_text_task = $format_text_to_remind->getFormatTaskText();

    if(isset($formatted_text_task)){
        $chat_work_api->sendTask($formatted_text_task, 0, $myself_data['room_id'],
                $myself_data['account_id'], $today);
    }else{
        echo '見直しするタスク(期限が8日前)が存在しません.'.PHP_EOL;
    }

    if(isset($formatted_text_messages)){
        array_walk($formatted_text_messages,
                array($chat_work_api, 'sendMessage'), $myself_data['room_id']);
    }else{
        echo 'リマインドするタスク(期限が今日-7日前)が存在しません.'.PHP_EOL;
    }
}catch(Exception $e){
    echo $e->getMessage();
}
