# WP Clean Admin API 鏂囨。

## 1. 姒傝堪

WP Clean Admin 鎻愪緵浜嗕赴瀵岀殑 API 鎺ュ彛锛屽厑璁稿紑鍙戣€呮墿灞曞拰瀹氬埗鎻掍欢鍔熻兘銆傛湰鏂囨。璇︾粏浠嬬粛浜嗘彃浠剁殑鏍稿績 API銆侀挬瀛愬拰鎵╁睍鏈哄埗銆?
## 2. 鏍稿績绫?
### 2.1 WPCleanAdmin\Core

鏍稿績绫绘槸鎻掍欢鐨勫叆鍙ｇ偣锛岃礋璐ｅ垵濮嬪寲鎵€鏈夋ā鍧椼€?
#### 鏂规硶

- **getInstance()**: 鑾峰彇鍗曚緥瀹炰緥
  - 杩斿洖: `WPCleanAdmin\Core` 瀹炰緥

- **init()**: 鍒濆鍖栨彃浠?  - 杩斿洖: `void`

- **activate()**: 鎻掍欢婵€娲诲洖璋?  - 杩斿洖: `void`

- **deactivate()**: 鎻掍欢鍋滅敤鍥炶皟
  - 杩斿洖: `void`

### 2.2 WPCleanAdmin\Settings

璁剧疆绠＄悊绫伙紝璐熻矗澶勭悊鎻掍欢璁剧疆銆?
#### 鏂规硶

- **getInstance()**: 鑾峰彇鍗曚緥瀹炰緥
  - 杩斿洖: `WPCleanAdmin\Settings` 瀹炰緥

- **get_settings()**: 鑾峰彇鎻掍欢璁剧疆
  - 鍙傛暟: `$key` (鍙€? - 璁剧疆閿悕
  - 杩斿洖: `array|mixed` 璁剧疆鍊?
- **update_settings()**: 鏇存柊鎻掍欢璁剧疆
  - 鍙傛暟: `$settings` - 璁剧疆鏁扮粍
  - 杩斿洖: `bool` 鏇存柊缁撴灉

### 2.3 WPCleanAdmin\Extension_API

鎵╁睍 API 绫伙紝鍏佽寮€鍙戣€呭垱寤烘彃浠舵墿灞曘€?
#### 鏂规硶

- **getInstance()**: 鑾峰彇鍗曚緥瀹炰緥
  - 杩斿洖: `WPCleanAdmin\Extension_API` 瀹炰緥

- **register_extension()**: 娉ㄥ唽鎵╁睍
  - 鍙傛暟: `$extension_data` - 鎵╁睍鏁版嵁
  - 杩斿洖: `bool` 娉ㄥ唽缁撴灉

- **execute_in_sandbox()**: 鍦ㄦ矙绠变腑鎵ц鎵╁睍浠ｇ爜
  - 鍙傛暟: `$extension_code` - 鎵╁睍浠ｇ爜
  - 鍙傛暟: `$options` (鍙€? - 娌欑閫夐」
  - 杩斿洖: `array` 鎵ц缁撴灉

### 2.4 WPCleanAdmin\Error_Handler

閿欒澶勭悊绫伙紝璐熻矗缁熶竴鐨勯敊璇鐞嗗拰鏃ュ織璁板綍銆?
#### 鏂规硶

- **getInstance()**: 鑾峰彇鍗曚緥瀹炰緥
  - 杩斿洖: `WPCleanAdmin\Error_Handler` 瀹炰緥

- **log_message()**: 璁板綍鏃ュ織娑堟伅
  - 鍙傛暟: `$message` - 鏃ュ織娑堟伅
  - 鍙傛暟: `$level` (鍙€? - 鏃ュ織绾у埆
  - 杩斿洖: `void`

## 3. 閽╁瓙鍜岃繃婊ゅ櫒

### 3.1 鍔ㄤ綔閽╁瓙

- **wpca_init**: 鎻掍欢鍒濆鍖栧畬鎴愬悗瑙﹀彂
- **wpca_settings_saved**: 璁剧疆淇濆瓨鍚庤Е鍙?- **wpca_extension_activated**: 鎵╁睍婵€娲诲悗瑙﹀彂
- **wpca_extension_deactivated**: 鎵╁睍鍋滅敤鏃惰Е鍙?
### 3.2 杩囨护鍣ㄩ挬瀛?
- **wpca_settings**: 杩囨护鎻掍欢璁剧疆
- **wpca_menu_items**: 杩囨护鍚庡彴鑿滃崟椤?- **wpca_dashboard_widgets**: 杩囨护浠〃鐩樺皬宸ュ叿
- **wpca_sandbox_enabled**: 鎺у埗娌欑鏄惁鍚敤

## 4. 鎵╁睍寮€鍙?
### 4.1 鍒涘缓鎵╁睍

鎵╁睍鏄?WP Clean Admin 鐨勫姛鑳芥ā鍧楋紝鍙互閫氳繃浠ヤ笅鏂瑰紡鍒涘缓锛?
```php
// 娉ㄥ唽鎵╁睍
$extension_api = WPCleanAdmin\Extension_API::getInstance();

$extension_data = array(
    'id' => 'my_extension',
    'name' => '鎴戠殑鎵╁睍',
    'version' => '1.0.0',
    'description' => '杩欐槸涓€涓ず渚嬫墿灞?,
    'author' => '寮€鍙戣€呭悕绉?,
    'file' => __FILE__,
    'active' => true
);

$extension_api->register_extension($extension_data);
```

### 4.2 鎵╁睍娌欑

鎵╁睍浠ｇ爜鍦ㄥ畨鍏ㄧ殑娌欑鐜涓墽琛岋紝闄愬埗浜嗗唴瀛樹娇鐢ㄥ拰鎵ц鏃堕棿锛?
```php
// 鍦ㄦ矙绠变腑鎵ц浠ｇ爜
$result = $extension_api->execute_in_sandbox(
    'echo "Hello from sandbox!";',
    array(
        'memory_limit' => '32M',
        'time_limit' => 3
    )
);

// 妫€鏌ユ墽琛岀粨鏋?if ($result['success']) {
    echo '鎵ц鎴愬姛: ' . $result['result'];
} else {
    echo '鎵ц澶辫触: ' . $result['error'];
}
```

## 5. 鏍稿績鍑芥暟

### 5.1 wpca_get_settings()

鑾峰彇鎻掍欢璁剧疆銆?
**鍙傛暟**:
- `$key` (鍙€?: 璁剧疆閿悕
- `$default` (鍙€?: 榛樿鍊?
**杩斿洖**:
- `mixed`: 璁剧疆鍊?
### 5.2 wpca_update_settings()

鏇存柊鎻掍欢璁剧疆銆?
**鍙傛暟**:
- `$settings`: 璁剧疆鏁扮粍

**杩斿洖**:
- `bool`: 鏇存柊缁撴灉

### 5.3 wpca_clean_admin_bar()

娓呯悊绠＄悊鏍忋€?
**鍙傛暟**:
- 鏃?
**杩斿洖**:
- `void`

### 5.4 wpca_clean_dashboard()

娓呯悊浠〃鐩樸€?
**鍙傛暟**:
- 鏃?
**杩斿洖**:
- `void`

## 6. 绀轰緥浠ｇ爜

### 6.1 鍒涘缓绠€鍗曟墿灞?
```php
<?php
/**
 * 绀轰緥鎵╁睍
 *
 * @package WPCleanAdmin
 * @version 1.0.0
 */

// 纭繚鎻掍欢宸插姞杞?if (class_exists('WPCleanAdmin\Extension_API')) {
    // 娉ㄥ唽鎵╁睍
    $extension_api = WPCleanAdmin\Extension_API::getInstance();
    
    $extension_data = array(
        'id' => 'example_extension',
        'name' => '绀轰緥鎵╁睍',
        'version' => '1.0.0',
        'description' => '涓€涓畝鍗曠殑绀轰緥鎵╁睍',
        'author' => '寮€鍙戣€?,
        'file' => __FILE__,
        'active' => true
    );
    
    $extension_api->register_extension($extension_data);
    
    // 娣诲姞璁剧疆椤?    add_filter('wpca_settings', function($settings) {
        $settings['example'] = array(
            'enabled' => true,
            'option1' => 'value1'
        );
        return $settings;
    });
    
    // 娣诲姞鑿滃崟椤?    add_filter('wpca_menu_items', function($items) {
        $items['example'] = array(
            'title' => '绀轰緥鑿滃崟椤?,
            'capability' => 'manage_options',
            'menu_slug' => 'wpca-example',
            'callback' => 'example_menu_callback'
        );
        return $items;
    });
    
    // 鑿滃崟椤瑰洖璋?    function example_menu_callback() {
        echo '<div class="wrap">';
        echo '<h1>绀轰緥椤甸潰</h1>';
        echo '<p>杩欐槸绀轰緥鎵╁睍鐨勯〉闈?/p>';
        echo '</div>';
    }
}
```

### 6.2 浣跨敤閿欒澶勭悊

```php
<?php
// 鑾峰彇閿欒澶勭悊鍣ㄥ疄渚?$error_handler = WPCleanAdmin\Error_Handler::getInstance();

// 璁板綍涓嶅悓绾у埆鐨勬棩蹇?$error_handler->log_message('杩欐槸涓€鏉¤皟璇曚俊鎭?, 'debug');
$error_handler->log_message('杩欐槸涓€鏉′俊鎭?, 'info');
$error_handler->log_message('杩欐槸涓€鏉¤鍛?, 'warning');
$error_handler->log_message('杩欐槸涓€鏉￠敊璇?, 'error');
$error_handler->log_message('杩欐槸涓€鏉′弗閲嶉敊璇?, 'critical');

// 璁剧疆鏃ュ織绾у埆
$error_handler->set_log_level('info');

// 鑾峰彇褰撳墠鏃ュ織绾у埆
$current_level = $error_handler->get_log_level();
echo '褰撳墠鏃ュ織绾у埆: ' . $current_level;
```

## 7. 鏈€浣冲疄璺?
1. **浣跨敤鍛藉悕绌洪棿**: 鎵€鏈夋墿灞曚唬鐮佸簲浣跨敤鍛藉悕绌洪棿锛岄伩鍏嶅啿绐?2. **閬靛惊缂栫爜瑙勮寖**: 閬靛惊 WordPress 鍜?PHP 缂栫爜瑙勮寖
3. **瀹夊叏绗竴**: 涓嶈鍦ㄦ墿灞曚腑浣跨敤鍗遍櫓鍑芥暟锛屽 `eval()`銆乣exec()` 绛?4. **鎬ц兘浼樺寲**: 閬垮厤闀挎椂闂磋繍琛岀殑鎿嶄綔锛屼娇鐢ㄧ紦瀛樻満鍒?5. **閿欒澶勭悊**: 浣跨敤鎻掍欢鐨勯敊璇鐞嗘満鍒惰褰曢敊璇?6. **鏂囨。瀹屽杽**: 涓烘墿灞曠紪鍐欒缁嗙殑鏂囨。

## 8. 鏁呴殰鎺掗櫎

### 8.1 甯歌闂

- **鎵╁睍娉ㄥ唽澶辫触**: 妫€鏌ユ墿灞曟暟鎹槸鍚﹀畬鏁达紝鐗瑰埆鏄?`id` 鍜?`file` 瀛楁
- **娌欑鎵ц澶辫触**: 妫€鏌ヤ唬鐮佹槸鍚︽湁璇硶閿欒锛屾垨鏄惁瓒呭嚭浜嗗唴瀛樺拰鏃堕棿闄愬埗
- **鏉冮檺闂**: 纭繚鐢ㄦ埛鏈夎冻澶熺殑鏉冮檺鎵ц鎿嶄綔

### 8.2 璋冭瘯鎶€宸?
- **鍚敤璋冭瘯妯″紡**: 鍦?`wp-config.php` 涓缃?`WP_DEBUG` 鍜?`WP_DEBUG_LOG`
- **鏌ョ湅鏃ュ織**: 妫€鏌?`wp-content/debug.log` 鍜屾彃浠剁殑 `logs` 鐩綍
- **浣跨敤閿欒澶勭悊鍣?*: 浣跨敤 `Error_Handler` 璁板綍璇︾粏鐨勯敊璇俊鎭?
## 9. 鐗堟湰鍘嗗彶

- **1.8.0**: 娣诲姞浜嗘墿灞?API 鍜屾矙绠辨墽琛岀幆澧?- **1.7.0**: 閲嶆瀯浜嗚缃鐞嗙郴缁?- **1.6.0**: 娣诲姞浜嗛敊璇鐞嗗拰鏃ュ織璁板綍鍔熻兘
- **1.5.0**: 瀹炵幇浜嗘ā鍧楀寲鏋舵瀯

## 10. 鑱旂郴涓庢敮鎸?
- **GitHub 浠撳簱**: https://github.com/sutchan/WP-Clean-Admin
- **闂鍙嶉**: https://github.com/sutchan/WP-Clean-Admin/issues
- **璐＄尞浠ｇ爜**: 娆㈣繋鎻愪氦 Pull Request

---

鏈枃妗ｇ敱 WP Clean Admin 鍥㈤槦缁存姢锛屽鏈変换浣曠枒闂垨寤鸿锛岃闅忔椂鍙嶉銆?
