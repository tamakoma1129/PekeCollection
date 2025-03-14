# 始め方
## 要件
ぺけコレクション(以下、ペケコレ)はDockerを利用した環境構築を想定しており、 DockerとDocker-Composeのインストールが必要です。

また、本番環境と開発環境でインストール方法が別れています。

以下の順でインストールをしてください。
1. インストール前準備
2. 本番環境 or 開発環境のインストール

## インストール前準備
`docker/.env.example`を参考に、envファイル`docker/.env`を作成します。

`docker/.env.example`の中身の通り、環境変数を指定します。

プロジェクトファイルはSSDに、メディアファイルの保存場所はHDDにすることを推奨しています。

## 本番環境のインストール
`PekeCollection/docker`で以下を実行しビルドする
```shell
docker compose build
```

ビルドが終わったら同じ位置で
```shell
docker compose up
```
を実行することで、[http://127.0.0.1/](http://127.0.0.1/)にアクセスできるようになり、使えるようになっている。

## 開発環境のインストール
### 本番環境との違い
開発環境では本番環境と違い、**コードの変更を追跡しホットリロードする**ようになっている。  
逆に本番環境では**コードを変更しても再ビルドしない限りは変更が反映されない**。

ただ、Jobに関する`PekeCollection/app/Jobs/`内コードはサーバーを再起動しない限り更新されない仕様なので注意。

### インストール方法
`PekeCollection/docker`で以下を実行しビルドする
```shell
docker compose -f docker-compose.dev.yaml build
```

ビルドが終わったら同じ位置で
```shell
docker compose -f docker-compose.dev.yaml up
```

