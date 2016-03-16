<?php

/**
 * ChatWorkのリマインダーの通知内容を整形する
 *
 * @author yaginuuu <yaginuma.takuya@hamee.co.jp>
 * @copyright Hamee.inc All Rights Reserved
 */
class FormatTextToRemindForChatWork{
    const CHAT_WORK_HOST_URL = 'https://kcw.kddi.ne.jp';
    private $tasks;

    public function __construct($tasks) {
        if(empty($tasks)) throw new Exception('タスク情報を入力してください.');
        $this->tasks = $tasks;
    }

    /**
     *  リマインドメッセージの整形を行う
     *
     * ・期限が今日のタスクから1週間前までの期限切れのタスクを降順に並び替える
     * @return array タスク期限ごとのメッセージの配列
     */
    public function getFormatMessageText(){
        $deadline_list = $this->getFormatTasks();
        $today = strtotime(date('Y/m/d'));
        $message_text = array();

        foreach($deadline_list as $deadline => $tasks){
            $deadline = strtotime(date('Y/m/d', $deadline));

            foreach($tasks as $key => $task){
                $target_task =
                    $target_task.
                    "by: [piconname:{$task['assigned_by_account_id']}]".PHP_EOL
                    .$task['message'].PHP_EOL
                    .self::CHAT_WORK_HOST_URL
                    ."/#!rid{$task['room_id']}-{$task['message_id']}".PHP_EOL;
                if(isset($tasks[$key + 1])){
                    $target_task = $target_task.'[hr]';
                }
            }
            if($deadline === $today){
                //今日のタスクを格納
                $message_box =
                    "[info][title](*)期日は今日です！終わらせましょう！(*)[/title]"
                    .$target_task.'[/info]';
                array_unshift($message_text, $message_box);
            }else{
                //期限切れのタスクを格納
                $limit_time = date('Y年m月d日', $deadline);
                $message_box =
                    "[info][title]期日は{$limit_time}まで[/title]"
                    .$target_task.'[/info]';
                $message_text[] = $message_box;
            }
            $target_task = null;
        }

        if(empty($message_text) === 0){
            echo 'メッセージテキストが存在しません.';
        }else{
            return $message_text;
        }
    }

    /**
     * ChatWorkのタスク情報を整形する
     *
     * 1. タスク情報(json)を以下の条件で限定
     *  ・期限なしのタスクは排除
     *  ・期限が明日以降のタスクは排除
     *  ・期限が1週間以前のタスクは排除
     * 2. タスクの期限(limit_time)をkeyとして, タスク情報をハッシュ化
     *  ・task_id    => タスクのID
     *  ・room_id    => タスクが所属するroomのID
     *  ・message_id => メッセージID
     *  ・message    => タスクの詳細(全角50文字のみ表示する)
     *  ・assigned_by_account_id => タスクを作成したアカウントID
     *
     * @return array タスク情報の連想配列の配列
     */
    private function getFormatTasks(){
        $last_week = strtotime(date('c', strtotime('-1 week')));
        $tomorrow = strtotime(date('c', strtotime('+1 day')));

        foreach($this->tasks as $task){
            if($task['limit_time'] === 0) continue;
            if($tomorrow < $task['limit_time']) continue;
            if($task['limit_time'] < $last_week) continue;

            $deadline_list[$task['limit_time']][] = array(
                    'task_id'                => $task['task_id'],
                    'room_id'                => $task['room']['room_id'],
                    'message_id'             => $task['message_id'],
                    'message'                => mb_strimwidth($task['body'], 0, 103, '...'),
                    'assigned_by_account_id' => $task['assigned_by_account']['account_id']
                    );
        }
        $isKrsort = krsort($deadline_list);

        if($isKrsort === false){
            echo 'ソートできません.';
        }else{
            return $deadline_list;
        }
    }
}