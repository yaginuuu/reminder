<?php

/**
 * ChatWorkAPIを利用する
 *
 * @author yaginuuu <yaginuma.takuya@hamee.co.jp>
 */
class ChatWorkApi{
    const HOST_NAME = 'https://api.chatwork.com';
    private $chat_work_token;

    public function __construct($chat_work_token) {
        $this->chat_work_token = $chat_work_token;
    }

    /**
     * ChatWork情報を取得する
     *
     * 1. HTTP通信
     * 2. ChatWork情報を取得
     * 3. ChatWork情報をパース
     *
     * @param string end_point_url ChatWorkAPIのエンドポイント
     * @return array 取得した情報の連想配列の配列
     **/
    public function get($end_point_url){
        $chat_work_data = $this->execCurl($end_point_url, null, null, null);

        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $json_decode_data =  $json->decode($chat_work_data);

        if(empty($json_decode_data)){
            return null;
        }else{
            return $json_decode_data;
        }
    }

    /**
     * ChatWork情報を送信する
     *
     * @param string end_point_url エンドポイントURL
     * @param string message_text 送信するメッセージテキスト
     * @param int to_id タスクの担当者ID
     * @param time limit タスクの期限
     * @return void
     */
    public function post($end_point_url, $message_text, $to_id, $limit){
        $this->execCurl($end_point_url, $message_text, $to_id, $limit);
    }

    /**
     * メッセージを送信する
     *
     * 指定したroomにメッセージを送信 ＊room_id, message_textは必須
     *
     * @param int room_id メッセージを送信するroom_id
     * @param string message_text 送信するメッセージテキスト
     * @return void
     */
    public function sendMessage($message_text, $key, $room_id){
        if(empty($room_id))      throw new Exception('ルームIDを入力してください.');
        if(empty($message_text)) throw new Exception('送信するメッセージを入力してください.');

        $end_point_url = "/v1/rooms/{$room_id}/messages";

        $this->post($end_point_url, $message_text, null, null);
    }

    /**
     * タスクを送信する
     *
     * 指定したroomにタスクを作成 ＊room_id, task_text, to_idは必須
     *
     * @param int room_id メッセージを送信するroomID
     * @param string task_text 送信するタスク概要
     * @param int to_id タスクの担当者
     * @param time limit タスクの期限
     * @return void
     */
    public function sendTask($task_text, $key, $room_id, $to_id, $limit){
        if(empty($to_id))     throw new Exception('タスク担当者IDを入力してください.');
        if(empty($task_text)) throw new Exception('送信するタスク概要を入力してください.');

        $end_point_url = "/v1/rooms/{$room_id}/tasks";

        $this->post($end_point_url, $task_text, $to_id, $limit);
    }

    /**
     * HTTP通信を行う
     *
     * 1. HTTP通信
     * 2. 通信結果を返す
     *
     * @param string end_point_url エンドポイントURL
     * @param string message_text 送信するメッセージテキストまたは送信するタスクの概要
     * @param int to_id タスクの担当者
     * @param time limit タスクの期限
     * @return array 通信結果
     */
    private function execCurl($end_point_url, $message_text, $to_id, $limit){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST_NAME.$end_point_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-ChatWorkToken: {$this->chat_work_token}"));
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($message_text)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(
                        array(
                            'body'   => $message_text,
                            'to_ids' => $to_id,
                            'limit'  => $limit
                            )));
        }
        $curl_data = curl_exec($ch);
        $errno     = curl_errno($ch);
        $error     = curl_error($ch);
        $info      = curl_getinfo($ch);

        if($errno) {
            throw new Exception('通信できませんでした. APIトークンを再度入力してください.');
        }

        curl_close($ch);
        return $curl_data;
    }
}
