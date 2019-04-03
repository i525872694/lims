#!/bin/bash

gitPath=".git/"
if [ -d "$gitPath" ] ; then
    echo "Git库已存在!"
else
    rm -rf app css js lib module template
    git clone gogs@leakview.vip:anheng_lims/cxlims.git tmp
    mv tmp/.git .
    mv tmp/.gitignore .
    mv tmp/* .
    rmdir tmp
    if [ -d "$gitPath" ] ; then
        git reset --hard HEAD
        git pull
        git config core.filemode false
        echo "Git已更新!"
    else
        echo "Git库更新失败";
    fi
fi
