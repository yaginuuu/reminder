# ChatWorkのタスクのリマインダ

## Overview
- 方法
    - マイチャットにリマインドする
- メッセージで通知
    - 期限前のタスク
    - 期限が過ぎているタスク
- タスクを作成
    - 期限が1週間過ぎたタスクについて, 見直し依頼するタスク

**＊期限前タスクについて, リマインド対象の期間はオプション指定できます.**  
**＊期限が過ぎているタスクについて, リマインド対象の期間はオプション指定できます.**  
**＊API制限により, 取得できるタスクは100件までとなっております.**

## Usage
```
$ git clone https://bitbucket.org/yaginuuu/task_reminder_for_chatwork
$ crontab -e
```
以下を入力してください.
```
0 10 * * 1-5 /usr/bin/php /Users/yaginuma.takuya/reminder/main.php arg1 arg2 arg3 arg4
```
phpのパス, 起動パスはご確認ください.  

example:
```
0 10 * * 1-5 /usr/bin/php /Users/yaginuma.takuya/reminder/main.php APIキー 5 3 ASC
```
**arg1:** 自分のChatWorkAPIキー. (必須)  
**arg2:** 期限が過ぎているタスクのリマインド対象の期間(day)を指定.  
　デフォルトは期限が過ぎて7日前まではリマインドする.(default=7)  
**arg3:** 期限前タスクのリマインド対象の期間(day)を指定.  
　デフォルトは期限が今日のタスクのみリマインドする.(default=1)  
**arg4:** リマインドするタスクの並び順(ASC or DESC)を指定. デフォルトは降順.(default=DESC)
