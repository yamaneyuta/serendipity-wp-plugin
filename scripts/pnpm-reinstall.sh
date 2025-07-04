#!/bin/bash

# pnpmで依存関係を再インストールするスクリプト
# package.jsonの依存関係を変更した後(特に削除時)に実行することを想定しています。

# pnpm-lock.yamlが存在するディレクトリで実行されていることを確認
if [ ! -f pnpm-lock.yaml ]; then
    echo "❌ [A16DC444] Error: pnpm-lock.yaml not found." >&2
    exit 1
fi

# .pnpm-storeディレクトリの内容を削除
echo "🗑️  [D9E2F7C6] Clean: .pnpm-store"
rm -rf .pnpm-store/* .pnpm-store/.[!.]* .pnpm-store/..?*

# pnpm-lock.yamlを削除
echo "🗑️  [D0542873] Remove: pnpm-lock.yaml"
rm -f pnpm-lock.yaml

# node_modulesディレクトリの内容を削除
find . -type d -name node_modules -not -path "*/node_modules/*/node_modules" | while read -r dir; do
    echo "🗑️  [2999296A] Clean: $dir"
    rm -rf "$dir"/* "$dir"/.[!.]* "$dir"/..?*
done

# pnpm installを実行
echo "🔄 [A1B2C3D4] Install: pnpm"
pnpm install

echo "✅ [1B88C30E] pnpm reinstall completed."
