
function main() {
	preformat_js
	format_js
	postformat_js
}

function preformat_js() {
	# `.gitignore`を元に`.prettierignore`を生成
	cp -f .gitignore .prettierignore

	echo "" >> .prettierignore
	echo "# Added by '$0'." >> .prettierignore

	# ドットで開始するディレクトリ内のファイルは対象外
	echo /\\.*/* >> .prettierignore

	# includesディレクトリ内のjsonファイルは対象外
	echo /includes/**/*.json >> .prettierignore

	# publicディレクトリ内のファイルは対象外
	echo /public/* >> .prettierignore
}

function format_js() {
	wp-scripts format
}

function postformat_js() {
	# `.prettierignore`を削除
	rm -f .prettierignore
}

main
