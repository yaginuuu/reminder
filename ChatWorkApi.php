<?php

/**
 * ChatWorkAPIを利用する
 *
 * @author yaginuuu <yaginuma.takuya@hamee.co.jp>
 * @copyright Hamee.inc All Rights Reserved
 */
class ChatWorkApi{
    const HOST_NAME = 'https://api.chatwork.com';
    private $chat_work_token;

    public function __construct($chat_work_token) {
        if(strlen($chat_work_token) === 0){
            echo 'APIキーを入力してください!';
        }else{
            $this->chat_work_token = $chat_work_token;
        }
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
        $chat_work_data = $this->execCurl($end_point_url, null);

        $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        $json_decode_data =  $json->decode($chat_work_data);

        if(empty($json_decode_data)){
            echo 'decodeできません.';
        }else{
            return $json_decode_data;
        }
    }

    /**
     * ChatWork情報を送信する
     *
     * @param string end_point_url エンドポイントURL
     * @param string message_text 送信するメッセージテキスト
     * @return void
     */
    public function post($end_point_url, $message_text){
        $this->execCurl($end_point_url, $message_text);
    }

    /**
     * メッセージを送信する
     *
     * 1. ChatWorkの指定したroomにメッセージを送信
     *
     * @param int room_id メッセージを送信するroom_id
     * @param string message_text 送信するメッセージテキスト
     * @return void
     */
    public function sendMessage($message_text, $key, $room_id){
        if(empty($room_id)) throw new Exception('ルームIDを入力してください.');
        if(empty($message_text)) throw new Exception('送信するメッセージを入力してください.');

        $end_point_url = "/v1/rooms/{$room_id}/messages";

        $this->post($end_point_url, $message_text);
    }

    /**
     * HTTP通信を行う
     *
     * 1. HTTP通信
     * 2. 通信結果を返す
     *
     * @param string end_point_url エンドポイントURL
     * @param string message_text 送信するメッセージテキスト
     * @return array 通信結果
     */
    private function execCurl($end_point_url, $message_text){
        if(empty($end_point_url)) throw new Exception('エンドポイントを入力してください.');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST_NAME.$end_point_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-ChatWorkToken: {$this->chat_work_token}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($message_text)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('body' => $message_text)));
        }
        $curl_data = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        if($errno) {
            $info = curl_getinfo($ch);
            throw new Exception('HTTPステータスコードは'.$info['http_code'].PHP_EOL
                    .$error);
        }

        curl_close($ch);
        return $curl_data;
    }
}
