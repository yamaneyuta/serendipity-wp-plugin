#!/bin/bash

function start() {
	main_compose=$(dirname $0)/compose.yml
	network_compose=$(dirname $0)/compose.network.yml

	compose_files_option="-f $main_compose -f $network_compose"

	# カレントユーザーが`runner`でない場合、開発環境と判断し、開発用のcomposeファイルを追加する。
	if [ "$(whoami)" != "runner" ]; then
		dev_only_compose=$(dirname $0)/compose.dev-only.yml
		compose_files_option="$compose_files_option -f $dev_only_compose"
	fi

	# `--force-recreate`: 何度か再起動等を行っているうちに存在しないネットワークに接続しようとするエラーが発生したため付与。
	# 参考: [Docker Network not Found](https://stackoverflow.com/questions/53347951/docker-network-not-found)
	docker compose $compose_files_option up -d --force-recreate
}

start
