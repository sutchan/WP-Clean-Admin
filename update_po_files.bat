@echo off

REM 确保中文显示正常
chcp 65001

REM 定义工具路径和文件路径
SET MSGMERGE="C:\Program Files (x86)\Poedit\GettextTools\bin\msgmerge.exe"
SET POT_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin.pot"
SET EN_PO_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin-en_US.po"
SET ZH_PO_FILE="e:\Dropbox\GitHub\WPCleanAdmin\wpcleanadmin\languages\wp-clean-admin-zh_CN.po"

REM 更新英文PO文件
echo 更新英文PO文件...
%MSGMERGE% --update %EN_PO_FILE% %POT_FILE%
if %ERRORLEVEL% EQU 0 (
    echo 英文PO文件更新成功！
) else (
    echo 英文PO文件更新失败，请检查错误信息。
)

REM 更新中文PO文件
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
echo 所有PO文件更新操作已完成！
echo ==========================================================
echo.
echo 请按任意键继续...
pause