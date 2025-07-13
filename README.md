
# TODOアプリ
-----
## プロジェクト概要

このプロジェクトは、XAMPP環境で動作していたPHP製のTODOアプリケーションを、Dockerコンテナ環境へ移行したものです。

  * **言語:** PHP
  * **データベース:** MariaDB
  * **機能:**
      * ユーザー登録とログイン
      * TODOタスクの作成、表示、管理

## クイックスタート

このプロジェクトをローカル環境で素早く起動するための手順です。

### 前提条件

  * [Docker](https://www.docker.com/) と Docker Compose がインストールされていること

### 1\. リポジトリをクローン

```bash
git clone https://github.com/TATSUNOBUETO/PHP-todo-app.git
cd PHP-todo-app
```


### 2\. Docker環境の起動

以下のコマンドを実行すると、Webサーバー、データベース、phpMyAdminの3つのコンテナが自動的に構築・起動されます。

```bash
docker-compose up -d --build
```

### 3\. ブラウザで起動

ブラウザで以下のURLにアクセスしてください。

  * **アプリケーション本体:** [http://localhost:8080](https://www.google.com/search?q=http://localhost:8080)
  * **phpMyAdmin:** [http://localhost:8081](https://www.google.com/search?q=http://localhost:8081)
  
### 4\. ログイン

  * **id:** admin
  * **pass:** admin

### 5\. アプリケーションの動作確認

アプリケーションは、ログイン後にTODOリストが表示されます。phpMyAdminにログインして、データベースが正しくインポートされているかも確認できます。

**phpMyAdminのログイン情報:**

  * **ユーザー名:** `admin`
  * **パスワード:** `admin_pass`