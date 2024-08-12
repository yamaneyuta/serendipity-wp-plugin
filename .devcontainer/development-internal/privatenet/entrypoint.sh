#!/bin/bash

# `serendipity-dev-blockchain`ディレクトリが存在しない場合はclone
# 存在する場合は最新の状態に更新
if [ ! -d serendipity-dev-blockchain ]; then
  echo "clone repository"
  git clone https://github.com/yamaneyuta/serendipity-dev-blockchain
  cd serendipity-dev-blockchain
else
	echo "update repository"
  cd serendipity-dev-blockchain
  # git pull
	git fetch origin main
	git reset --hard origin/main
fi

npm install
npm run start
