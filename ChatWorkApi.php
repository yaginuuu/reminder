<?php

/**
 * ChatWorkAPI経由で以下のことができる
 *
 * ・ChatWork情報の取得->get()
 * ・ChatWork情報の送信->post()
 * ・メッセージを送信->sendMessage()
 *
 * @author yaginuuu <yaginuma.takuya@hamee.co.jp>
 * @copyright Hamee.inc All Rights Reserved
 * @param string $chat_work_token ChatWorkAPIキー
 */
class ChatWorkApi{
    const HOST_NAME = 'https://api.chatwork.com';
    private $chat_work_token;

    public function __construct($chat_work_token) {
        if(empty($chat_work_token)) throw new Exception('APIキーが存在しません.');
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
     * @todo
     **/
    public function get($end_point_url){
        try{
            $chat_work_data = $this->execCurl($end_point_url, null);
        }catch(Exception $e){
            echo $e->getMessage();
        }

        // TODO: 変数チェック
        $json_decode_data = json_decode($chat_work_data, true);

        return $json_decode_data;
    }

    /**
     * ChatWork情報を送信する
     *
     * @param string end_point_url エンドポイントURL
     * @param string message_text 送信するメッセージテキスト
     * @return void
     * @todo
     */
    public function post($end_point_url, $message_text){
        try{
            $this->execCurl($end_point_url, $message_text);
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * メッセージを送信する
     *
     * 1. ChatWorkの指定したroomにメッセージを送信
     *
     * @param int room_id メッセージを送信するroom_id
     * @param string message_text 送信するメッセージテキスト
     * @return void
     * @todo
     */
    public function sendMessage($room_id, $message_text){
        if(empty($room_id)) throw new Exception('ルームIDが存在しません.');
        if(empty($message_text)) throw new Exception('送信するメッセージが存在しません.');

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
     * @todo
     */
    private function execCurl($end_point_url, $message_text){
        if(empty($end_point_url)) throw new Exception('エンドポイントが存在しません.');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::HOST_NAME.$end_point_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-ChatWorkToken: {$this->chat_work_token}"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(isset($message_text)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['body' => $message_text]));
        }
        $curl_data = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if (CURLE_OK !== $errno) {
            throw new Exception($error, $errno);
        }
        return $curl_data;
    }
}
