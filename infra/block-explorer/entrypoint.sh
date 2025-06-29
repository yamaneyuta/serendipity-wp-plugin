#!/bin/bash

# `block-explorer`ディレクトリが存在しない場合はclone
# 存在する場合は最新の状態に更新
if [ ! -d block-explorer ]; then
	echo "clone repository"
	git clone https://github.com/Shaivpidadi/block-explorer
	cd block-explorer
else
	echo "update repository"
	cd block-explorer
	# git pull
	git fetch origin main
	git reset --hard origin/main
fi

npm install
npm run start
