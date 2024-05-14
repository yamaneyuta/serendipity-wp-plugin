# nodejsでの開発環境
#
# - Ubuntu
# - Node.js
# - git + lazygit
#
# 更新日時: 2024-05-13 19:42:00
#
# メモ:
# - hardhat 2.22.3がnodejs 22.x系で動作しないため、20.x系を指定
FROM ubuntu:24.04

RUN ln -sf /usr/share/zoneinfo/${TIMEZONE:-Asia/Tokyo} /etc/localtime

RUN apt update && \
    apt install git curl -y && \
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
    apt clean && rm -rf /var/lib/apt/lists/*
