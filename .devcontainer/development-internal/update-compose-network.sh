
# # dockerコンテナの一覧を取得
# docker_output=$(docker network ls)

# # `docker ps -a`の出力から、wp-cliコンテナのハッシュ値にあたる部分を抽出するスクリプト
# # [hash]_default
# awk_script='
# /[0-9a-f]{32}_default/ {
#     match($0, /[0-9a-f]{32}_default/)
#     if (RSTART != 0) {
#         print substr($0, RSTART, 32)
#         exit
#     }
# }'

# # dockerコンテナからハッシュ値を取得
# hash=$(echo "$docker_output" | awk "$awk_script")

# wp-envのキャッシュディレクトリは、`~/.wp-env`または`~/wp-env`のどちらか(WP=_ENV_HOMEを指定していない場合)
# https://github.com/WordPress/gutenberg/blob/2f30cddff15723ac7017fd009fc5913b7b419400/packages/env/lib/config/get-cache-directory.js#L9-L39
if [[ -d ~/.wp-env ]]; then
	cache_dir=~/.wp-env
elif [[ -d ~/wp-env ]]; then
	cache_dir=~/wp-env
else
	echo "Error: wp-env directory not found."
	exit 1
fi

# ~/.wp-env(~/wp-env)ディレクトリに存在するフォルダ名(MD5ハッシュ値)を取得
hash=$(ls $cache_dir | grep -E '^[0-9a-f]{32}$')

# ハッシュ値が取得できなかった場合はエラー
if [[ -z $hash ]]; then
	echo "Error: hash not found."
	exit 1
fi


# `compose.network.yml`ファイルを更新
docker_network_file_path=$(dirname $0)/compose.network.yml
echo "# This file is automatically generated by the $0 script" > $docker_network_file_path
echo "" >> $docker_network_file_path
echo "networks:" >> $docker_network_file_path
echo "  wpnetwork:" >> $docker_network_file_path
echo "    name: ${hash}_default" >> $docker_network_file_path
echo "    external: true" >> $docker_network_file_path
