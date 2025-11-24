# 更新所有PHP文件的头部注释，添加更新日期
$currentDate = Get-Date -Format "yyyy-MM-dd"

# 获取所有PHP文件
$phpFiles = Get-ChildItem -Path "e:\Dropbox\GitHub\WPCleanAdmin" -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    # 读取文件内容
    $content = Get-Content -Path $file.FullName -Raw
    
    # 检查是否已经包含更新日期
    if ($content -match "@updated") {
        Write-Host "文件 $($file.Name) 已包含更新日期，跳过..."
        continue
    }
    
    # 获取相对路径
    $relativePath = $file.FullName.Replace("e:\Dropbox\GitHub\WPCleanAdmin\", "")
    
    # 查找文件头部注释
    if ($content -match "/\*\*([\s\S]*?)\*/") {
        $header = $matches[0]
        
        # 检查是否已经有@file和@version标签
        if ($header -match "@file") {
            # 更新现有版本号和添加更新日期
            $newHeader = $header -replace "@version\s+\d+\.\d+\.\d+", "@version 1.7.13"
            $newHeader = $newHeader -replace "(\*/)", "`t* @updated $currentDate`n`n`$1"
        } else {
            # 添加新的文件信息
            $newHeader = $header -replace "(\*/)", "`t* @file $relativePath`n`t* @version 1.7.13`n`t* @updated $currentDate`n`n`$1"
        }
        
        # 替换文件内容
        $newContent = $content -replace "/\*\*([\s\S]*?)\*/", $newHeader
        
        # 写回文件
        Set-Content -Path $file.FullName -Value $newContent -NoNewline
        
        Write-Host "已更新文件: $($file.Name)"
    } else {
        Write-Host "文件 $($file.Name) 没有找到头部注释，跳过..."
    }
}

Write-Host "PHP文件更新完成！"