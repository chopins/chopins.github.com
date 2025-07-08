---
layout: blog_contents
title: github庞大项目中快速下载指定文件夹下的内容
categories: blog
---

这里以 https://github.com/elastic/built-docs 项目举例
1. 我们只需要下载 `html/en/elasticsearch/reference/8.x` 以及 `html/static` 这两个文件夹下的内容。
2. 首先初始化一个本地空仓库：`git init elastic-docs && cd elastic-docs`
3. 启用稀疏检出:`git config core.sparseCheckout true`
4. 指定目标目录
    ```bash
    echo "html/en/elasticsearch/reference/8.x/" >> .git/info/sparse-checkout
    echo "html/static/" >> .git/info/sparse-checkout
    ```
5. 添加远程仓库`git remote add origin git@github.com:elastic/built-docs.git`
6. 设置对象过滤
    ```bash
    git config --local remote.origin.promisor true
    git config --local remote.origin.partialclonefilter "blob:none tree:0"
    ```
7. 定位到最新提交 `git fetch --depth=1 --filter=tree:0 origin master`
8. 拉区代码 `git pull --depth=1  origin master`
