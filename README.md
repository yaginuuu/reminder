# ChatWorkのタスクのリマインダ

## Overview
- 方法
    - マイチャットにリマインドする
- メッセージで通知
    - 期限が今日のタスク
    - 期限が過ぎている1週間以内のタスク
- タスクを作成
    - 期限が1週間過ぎたタスクについて, 見直し依頼するタスク

＊

## Usage
```
$ git clone https://bitbucket.org/yaginuuu/task_reminder_for_chatwork
$ crontab -e
```
以下を入力してください.
```
0 10 * * 1-5 /usr/bin/php /Users/yaginuma.takuya/reminder/main.php 自分のChatWorkAPIトークン
```
＊phpのパス, 起動パスはご確認ください.
