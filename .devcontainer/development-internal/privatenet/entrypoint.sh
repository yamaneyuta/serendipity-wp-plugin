#!/bin/bash

# `serendipity-dev-blockchain`ディレクトリが存在しない場合はclone
# 存在する場合は最新の状態に更新
if [ ! -d serendipity-dev-blockchain ]; then
  git clone https://github.com/yamaneyuta/serendipity-dev-blockchain
  cd serendipity-dev-blockchain
else
  cd serendipity-dev-blockchain
  git pull
fi

npm install
npm run start
