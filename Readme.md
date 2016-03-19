# YniDB

データベース接続ユースリティ

## 環境

PHP > 5/3
mysqli拡張
sqlite3拡張

## 利用

    "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/yni3/YniDB.git"
            }
    ],
    "require": {
        "yni3/YniDB" : "dev-master"
    },
    "config": {
        "vendor-dir": "Vendor/"
    }

## 開発時
composer install --dev
./composer/bin/phpunit test/<test name>
