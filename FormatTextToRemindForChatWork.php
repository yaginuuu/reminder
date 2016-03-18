<?php

/**
 * ChatWorkのリマインダーの通知内容を整形する
 *
 * @author yaginuuu <yaginuma.takuya@hamee.co.jp>
 */
class FormatTextToRemindForChatWork{
    const CHAT_WORK_HOST_URL = 'https://kcw.kddi.ne.jp';
    private $tasks;

    public function __construct($tasks) {
        $this->tasks = $tasks;
    }

    /**
     *  リマインドするメッセージの整形を行う
     *
     * ・期限が過ぎているタスクについて, 1週間前またはオプションで指定された
     * 　期間までのタスクを降順またはオプションで指定された順に並び替える
     * ・期限前のタスクについて, 今日またはオプションで指定された
     *   期間までのタスクを降順またはオプションで指定された順に並び変える
     *
     * @param time from_date 取得する期限が過去のタスクの期間
     * @param time to_date 取得する期限が未来のタスクの期間
     * @param string order タスクの並び順
     * @return array タスク期限ごとのメッセージの配列
     */
    public function getFormatMessageText($from_date, $to_date, $order){
        $today     = strtotime(date('Y/m/d'));
        $from_date = strtotime(date('c', strtotime('-'.$from_date.' day')));
        $to_date   = strtotime(date('c', strtotime('+'.$to_date.' day')));
        $deadline_list = $this->getFormatTasks($from_date, $to_date, $order);
        $message_text  = array();
        $target_task   = null;
        $future_message_box = null;

        if(is_null($deadline_list)) return null;

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

            $limit_time = date('Y年m月d日', $deadline);
            //期限前のタスクを格納
            if($deadline > $today){
                $future_message_box =
                    $future_message_box.
                    "[info][title]期日は{$limit_time}まで[/title]"
                    .$target_task.'[/info]';
            }elseif($deadline === $today){
                $future_message_box =
                    $future_message_box.
                    "[info][title]期日は今日です！:D[/title]"
                    .$target_task.'[/info]';
            }else{
                //期限切れのタスクを格納
                $message_box =
                    "[info][title]期日が過ぎています！期日は{$limit_time}まで[/title]"
                    .$target_task.'[/info]';
                $message_text[] = $message_box;
            }
            //期限前のタスクをまとめる
            if($tasks === end($deadline_list) && isset($future_message_box)){
                $message_box =
                    "[info][title](*)もうすぐ期限です！(*)[/title]"
                    .$future_message_box.'[/info]';
                array_unshift($message_text, $message_box);
            }
            $target_task = null;
        }

        if(empty($message_text)){
            return null;
        }

        return $message_text;
    }

    /**
     *  見直しタスク(期限が8日前のタスク)の概要を整形
     *
     * 8日前のタスクの一覧とタスクの見直しを依頼するメッセージを作成する
     *
     * @return string task_text 見直しタスクの概要
     */
    public function getFormatTaskText(){
        $from_date        = strtotime(date('c', strtotime('-1 week -1 day')));
        $to_date          = strtotime(date('c', strtotime('-1 week')));
        $look_again_tasks = $this->getFormatTasks($from_date, $to_date, 'DESC');
        $target_task      = null;
        if(is_null($look_again_tasks)) return null;

        foreach($look_again_tasks as $deadline => $tasks){
            $limit_time = date('Y年m月d日', $deadline);
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
        }
        $task_text = '(*)タスクの見直し依頼(*)'.PHP_EOL
            .'以下のタスクの期限が過ぎています。'.PHP_EOL
            .'期限は'.$limit_time.'まででした。'.PHP_EOL
            .'タスクの見直しを行ってください！！'.PHP_EOL
            .'[info]'.$target_task.'[/info]';

        return $task_text;
    }

    /**
     * ChatWorkのタスク情報を期間を指定し, 整形する
     *
     * 1. タスク情報(json)を以下の条件で限定
     *  ・期限なしのタスクは排除
     *  ・指定されたfrom_dateよりも期限が過去のタスクは排除
     *  ・指定されたto_dateよりも期限が未来のタスクは排除
     * 2. タスクの期限(limit_time)をkeyとして, タスク情報をハッシュ化
     *  ・task_id    => タスクのID
     *  ・room_id    => タスクが所属するroomのID
     *  ・message_id => メッセージID
     *  ・message    => タスクの詳細(全角50文字のみ表示する)
     *  ・assigned_by_account_id => タスクを作成したアカウントID
     * 3. 指定されたタスクの並び順に並び変える
     *
     * @param time from_date 期間A (AからBまでの期間を指定)
     * @param time to_date   期間B (AからBまでの期間を指定)
     * @param string order   タスクの並び順
     * @return array タスク情報の連想配列の配列
     */
    private function getFormatTasks($from_date, $to_date, $order){
        foreach($this->tasks as $task){
            if($task['limit_time'] === 0) continue;
            if($task['limit_time'] < $from_date) continue;
            if($to_date < $task['limit_time']) continue;

            $deadline_list[$task['limit_time']][] = array(
                    'task_id'                => $task['task_id'],
                    'room_id'                => $task['room']['room_id'],
                    'message_id'             => $task['message_id'],
                    'message'                => mb_strimwidth($task['body'], 0, 103, '...'),
                    'assigned_by_account_id' => $task['assigned_by_account']['account_id']
                    );
        }
        if(empty($deadline_list)){
            return null;
        }else{
            if($order === 'ASC'){
                $isKrsort = ksort($deadline_list);
            }elseif($order === 'DESC'){
                $isKrsort = krsort($deadline_list);
            }else{
                throw new Exception('タスクの並び順のオプションを変更してください.');
            }
        }

        if($isKrsort === false){
            return null;
        }

        return $deadline_list;
    }
}
