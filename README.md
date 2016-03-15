# ChatWorkのタスクのリマインダ

## Overview
- 方法
    - マイチャットにリマインドする
- リマインド内容
    - 期限が今日のタスク
    - 期限が過ぎている1週間以内のタスク

## Usage
```
$ git clone https://bitbucket.org/yaginuuu/task_reminder_for_chatwork
$ crontab -e
```
以下を入力してください.
```
0 10 0 0 1-5 php_path ./task_reminder_for_chatwork/main.php 自分のChatWorkのAPIトークン
```
