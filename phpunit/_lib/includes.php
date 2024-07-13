<?php

// このファイルを除く、同一ディレクトリ内の.phpファイルを全て読み込む
foreach ( glob( __DIR__ . '/*.php' ) as $file ) {
	if ( $file !== __FILE__ ) {
		require_once $file;
	}
}

// サブディレクトリ内の.phpファイルを全て読み込む
$sub_dirs = glob( __DIR__ . '/*', GLOB_ONLYDIR );
foreach ( $sub_dirs as $sub_dir ) {
	foreach ( glob( $sub_dir . '/*.php' ) as $file ) {
		require_once $file;
	}
}
