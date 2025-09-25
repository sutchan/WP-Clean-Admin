@echo off

REM 确保中文显示正常
chcp 65001

REM 定义工具路径和文件路径
SET MSGFMT="C:\Program Files (x86)\Poedit\GettextTools\bin\msgfmt.exe"
SET MSGMERGE="C:\Program Files (x86)\Poedit\GettextTools\bin\msgmerge.exe"
SET POT_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin.pot"
SET EN_PO_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin-en_US.po"
SET ZH_PO_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin-zh_CN.po"
SET EN_MO_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin-en_US.mo"
SET ZH_MO_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin-zh_CN.mo"

REM 从POT文件更新PO文件
echo ==========================================================
echo 更新PO文件...
echo ===========================================================
echo 更新英文PO文件...
%MSGMERGE% --update %EN_PO_FILE% %POT_FILE%
if %ERRORLEVEL% EQU 0 (
    echo 英文PO文件更新成功！
) else (
    echo 英文PO文件更新失败，请检查错误信息。
)

echo.
echo 更新中文PO文件...
%MSGMERGE% --update %ZH_PO_FILE% %POT_FILE%
if %ERRORLEVEL% EQU 0 (
    echo 中文PO文件更新成功！
) else (
    echo 中文PO文件更新失败，请检查错误信息。
)

echo.
echo ==========================================================
echo 生成MO文件...
echo ===========================================================
REM 生成英文MO文件
echo 生成英文MO文件...
%MSGFMT% -o %EN_MO_FILE% %EN_PO_FILE%
if %ERRORLEVEL% EQU 0 (
    echo 英文MO文件生成成功！
) else (
    echo 英文MO文件生成失败，请检查错误信息。
)

REM 生成中文MO文件
echo.
echo 生成中文MO文件...
%MSGFMT% -o %ZH_MO_FILE% %ZH_PO_FILE%
if %ERRORLEVEL% EQU 0 (
    echo 中文MO文件生成成功！
) else (
    echo 中文MO文件生成失败，请检查错误信息。
)

echo.
echo ==========================================================
echo 翻译文件状态：
dir "e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\*.mo"

echo.
echo ==========================================================
echo 所有翻译文件已成功更新！
echo ==========================================================
echo.
echo 请按任意键继续...
pause