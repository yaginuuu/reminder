<?php

ini_set("display_errors", On);
error_reporting(E_ALL);
date_default_timezone_set('UTC');

require_once('JSON.php');
require_once('ChatWorkApi.php');
require_once('FormatTextToRemindForChatWork.php');

$options         = getopt('f:t:', array('token:', 'asc'));
$chat_work_token = isset($options['token']) ? $options['token'] : null;
$from_date       = isset($options['f']) && is_numeric($options['f']) ? $options['f'] : 7;
$to_date         = isset($options['t']) && is_numeric($options['t']) ? $options['t'] : 1;
$order           = isset($options['asc']) ? 'ASC' : 'DESC';

main($chat_work_token, $from_date, $to_date, $order);

function main($chat_work_token, $from_date, $to_date, $order){
    $today = time();
    try{
        if(isset($chat_work_token)){
            $chat_work_api   = new ChatWorkApi($chat_work_token);
            $chat_work_tasks = $chat_work_api->get('/v1/my/tasks?status=open');
            $myself_data     = $chat_work_api->get('/v1/me');

            if(isset($chat_work_tasks)){
                $format_text = new FormatTextToRemindForChatWork($chat_work_tasks);
                $formatted_text_messages =
                    $format_text->getFormatMessageText($from_date, $to_date, $order);
                $formatted_text_task = $format_text->getFormatTaskText();

                if(isset($formatted_text_task)){
                    $chat_work_api->sendTask($formatted_text_task, 0, $myself_data['room_id'],
                            $myself_data['account_id'], $today);
                    echo '見直し依頼タスクを作成しました！'.PHP_EOL;
                }else{
                    echo '見直しするタスクが存在しません.'.PHP_EOL;
                }

                if(isset($formatted_text_messages)){
                    array_walk($formatted_text_messages,
                            array($chat_work_api, 'sendMessage'), $myself_data['room_id']);
                    echo 'タスクをリマインドしました！'.PHP_EOL;
                }else{
                    echo 'リマインドするタスクが存在しません.'.PHP_EOL;
                }
            }else{
                echo 'ChatWorkのタスクが存在しないので, リマインドできません.';
            }
        }else{
            echo 'APIトークンを入力してください.'.PHP_EOL;
        }
    }catch(Exception $e){
        echo $e->getMessage();
    }
}
