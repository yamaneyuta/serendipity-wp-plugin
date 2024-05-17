#!/bin/bash

function start() {
	main_compose=$(dirname $0)/compose.yml
	network_compose=$(dirname $0)/compose.network.yml

	# `--force-recreate`: 何度か再起動等を行っているうちに存在しないネットワークに接続しようとするエラーが発生したため付与。
	# 参考: [Docker Network not Found](https://stackoverflow.com/questions/53347951/docker-network-not-found)
	docker compose -f .devcontainer/development-internal/compose.yml -f .devcontainer/development-internal/compose.network.yml up -d --force-recreate
}

start
