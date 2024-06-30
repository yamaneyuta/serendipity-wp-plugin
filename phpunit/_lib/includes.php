<?php

// このファイルを除く、同一ディレクトリ内の.phpファイルを全て読み込む
foreach ( glob( __DIR__ . '/*.php' ) as $file ) {
	if ( $file !== __FILE__ ) {
		require_once $file;
	}
}
