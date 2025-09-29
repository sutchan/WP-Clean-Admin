@echo off
chcp 65001 >nul

REM 这个批处理文件用于生成WP Clean Admin插件的MO翻译文件
REM 尝试多种方式寻找PHP解释器

REM 方式1: 检查系统PATH中是否有PHP
where php >nul 2>nul
if %errorlevel% equ 0 (
    echo 正在使用系统PATH中的PHP生成MO文件...
    php wpcleanadmin\languages\generate-mo.php
    goto end
)

REM 方式2: 检查常见的PHP安装路径
set "PHP_PATHS[0]=C:\xampp\php\php.exe"
set "PHP_PATHS[1]=C:\wamp\bin\php\php*\php.exe"
set "PHP_PATHS[2]=C:\UwAmp\bin\php\php*\php.exe"
set "PHP_PATHS[3]=%ProgramFiles%\PHP\php.exe"
set "PHP_PATHS[4]=%ProgramFiles(x86)%\PHP\php.exe"

for /l %%i in (0,1,4) do (
    if exist "!PHP_PATHS[%%i]!" (
        echo 正在使用 !PHP_PATHS[%%i]! 生成MO文件...
        "!PHP_PATHS[%%i]!" wpcleanadmin\languages\generate-mo.php
        goto end
    )
)

REM 方式3: 如果以上都失败，提示用户手动设置PHP路径
:manual_php
cls
echo 未找到PHP解释器。
echo.
echo 请手动输入您的PHP可执行文件路径（例如: C:\xampp\php\php.exe）
echo 或者按Enter键直接退出。
echo.
set /p PHP_PATH=请输入PHP路径: 

if not defined PHP_PATH (
    echo 操作已取消。
    pause
    exit /b 1
)

if not exist "%PHP_PATH%" (
    echo 错误: 找不到指定的PHP文件 "%PHP_PATH%"
    pause
    goto manual_php
)

REM 使用用户提供的PHP路径
"%PHP_PATH%" wpcleanadmin\languages\generate-mo.php

:end
REM 检查MO文件是否生成成功
if exist "wpcleanadmin\languages\wp-clean-admin-zh_CN.mo" (
    echo MO文件生成成功！
) else (
    echo 警告: 未检测到生成的MO文件，可能生成过程中出现了问题。
    echo 请检查PHP安装是否正确，以及generate-mo.php文件是否存在且无错误。
)

pause