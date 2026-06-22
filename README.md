# laravel+Fortify

## 概要

laravel+Fortifyのテンプレートです。

## 環境構築

### ・Dockerビルド

```
git clone git@github.com:saku-taro/template-fortify.git
```

```
docker-compose up -d --build
```

### ・Laravel環境構築

・プロジェクトのルートディレクトリ（docker-compose.ymlがある場所）へ移動し、以下のコマンドを実行してください。

```
docker-compose exec php bash
```

```
composer install
```

```
cp .env.example .env
```

---

・「.envファイル」の環境変数を次の通り変更する

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```
