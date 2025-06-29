# 2024-06-12時点で`npx playwright install --with-deps`コマンドがUbuntu24.04に対応していないためUbuntu22.04を使用
# https://github.com/microsoft/playwright/issues/30368

FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# sudo: `wp-env`を`root`以外のユーザーで実行するため
RUN apt update && \
    apt install git curl sudo -y && \
    \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install nodejs -y && \
    \
    LAZYGIT_VERSION=$(curl -s "https://api.github.com/repos/jesseduffield/lazygit/releases/latest" | grep -Po '"tag_name": "v\K[^"]*') && \
    curl -Lo lazygit.tar.gz "https://github.com/jesseduffield/lazygit/releases/latest/download/lazygit_${LAZYGIT_VERSION}_Linux_x86_64.tar.gz" && \
    tar xf lazygit.tar.gz lazygit && \
    install lazygit /usr/local/bin && \
    rm -rf lazygit* && \
    \
	npm install -g pnpm@10.12.2 && \
    apt clean && rm -rf /var/lib/apt/lists/*

# PHP及び拡張モジュールをインストール
ARG PHP_VERSION
ENV PHP_VERSION=${PHP_VERSION}

RUN apt update && \
    apt install software-properties-common -y && \
    add-apt-repository ppa:ondrej/php -y && \
    apt update && \
    apt install unzip -y && \
    \
    apt install \
		php${PHP_VERSION} \
		php${PHP_VERSION}-curl \
		php${PHP_VERSION}-dom \
		php${PHP_VERSION}-zip \
		php${PHP_VERSION}-mbstring \
		php${PHP_VERSION}-gmp \
		-y && \
    \
    apt clean && rm -rf /var/lib/apt/lists/*

# Composerをインストール
RUN php -r "copy('https://getcomposer.org/download/2.7.6/composer.phar', '/usr/local/bin/composer');" && \
    chmod +x /usr/local/bin/composer

# vscodeユーザーが存在しない場合は追加
RUN id vscode > /dev/null 2>&1 || useradd -m -s /bin/bash vscode

# vscodeユーザーはパスワード無しでsudoが実行できるようにする
RUN echo "vscode ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers.d/vscode

# ここからvscodeユーザーで操作
USER vscode
WORKDIR /home/vscode

# `~/.config/composer/vendor/bin`は、`composer global require`でインストールされるディレクトリ
# (`composer global about`で確認可能)
ARG COMPOSER_GLOBAL_BIN_DIR=~/.config/composer/vendor/bin

# PHPCSをグローバルにインストール(バージョン変更時はGitHub Actionsの設定も見直すこと)
ARG PHPCS_VERSION=3.9.2
RUN composer global require squizlabs/php_codesniffer="${PHPCS_VERSION}"
# ログイン時にPATHを通すため、`~/.bashrc`に追記
RUN echo "export PATH=\$PATH:${COMPOSER_GLOBAL_BIN_DIR}" >> ~/.bashrc

# wpcsをグローバルにインストール(バージョン変更時はGitHub Actionsの設定も見直すこと)
ARG WPCS_VERSION=3.1.0
RUN composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
RUN composer global require --dev wp-coding-standards/wpcs:"${WPCS_VERSION}"


# storybookテスト用にplaywrightをインストール
RUN yes | npx playwright install --with-deps

