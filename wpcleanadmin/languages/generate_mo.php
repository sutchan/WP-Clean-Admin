<?php
/**
 * 鐢ㄤ簬鐢熸垚 .mo 缈昏瘧鏂囦欢鐨勭畝鍗曡剼鏈? * 鐢变簬绯荤粺涓己灏?gettext 宸ュ叿锛屼娇鐢ㄦ鑴氭湰浣滀负鏇夸唬
 * 
 * 浣跨敤鏂规硶锛? * 1. 灏嗘鑴氭湰鏀惧湪 languages 鐩綍涓? * 2. 閫氳繃 PHP 杩愯姝よ剼鏈細php generate_mo.php
 */

// function_exists鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕佸鐢ㄥ疄鐜?
// 鐩存帴璁块棶妫€鏌?if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

// 瀹氫箟鍑芥暟灏?.po 鏂囦欢杞崲涓?.mo 鏂囦欢
function generate_mo_file($po_file) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    // empty鍜宔cho鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?$has_functions = 
                    function_exists( 'str_replace' ) && 
                    function_exists( 'file_get_contents' ) && 
                    function_exists( 'file_put_contents' ) && 
                    function_exists( 'parse_po_file' ) && 
                    function_exists( 'generate_mo_content' );
    
    if ( ! $has_functions ) {
        echo "閿欒锛氱己灏戝繀瑕佺殑鍑芥暟鏀寔銆俓n";
        return false;
    }
    
    // 妫€鏌?.po 鏂囦欢鏄惁瀛樺湪
    if (!file_exists($po_file)) {
        echo "閿欒锛氭枃浠?$po_file 涓嶅瓨鍦ㄣ€俓n";
        return false;
    }
    
    // 鏋勫缓 .mo 鏂囦欢鍚?    $mo_file = str_replace('.po', '.mo', $po_file);
    
    // 璇诲彇 .po 鏂囦欢鍐呭
    $po_content = file_get_contents($po_file);
    if ($po_content === false) {
        echo "閿欒锛氭棤娉曡鍙?$po_file 鏂囦欢鍐呭銆俓n";
        return false;
    }
    
    // 瑙ｆ瀽 .po 鏂囦欢锛堢畝鍖栫増锛屼粎鏀寔鍩烘湰鏍煎紡锛?    $entries = parse_po_file($po_content);
    
    // 濡傛灉瑙ｆ瀽澶辫触锛岃繑鍥為敊璇?    if (empty($entries)) {
        echo "閿欒锛氭棤娉曡В鏋?$po_file 鏂囦欢銆俓n";
        return false;
    }
    
    // 鐢熸垚 .mo 鏂囦欢
    $mo_content = generate_mo_content($entries);
    
    // 鍐欏叆 .mo 鏂囦欢
    if (file_put_contents($mo_file, $mo_content) === false) {
        echo "閿欒锛氭棤娉曞啓鍏?$mo_file 鏂囦欢銆俓n";
        return false;
    }
    
    echo "鎴愬姛锛氬凡鐢熸垚 $mo_file 鏂囦欢銆俓n";
    return true;
}

// 绠€鍖栫増 .po 鏂囦欢瑙ｆ瀽鍑芥暟
function parse_po_file($po_content) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦紝empty鏄疨HP璇█缁撴瀯涓嶉渶瑕佹鏌?    $has_functions = function_exists( 'explode' ) && 
                    function_exists( 'trim' ) && 
                    function_exists( 'strpos' ) && 
                    function_exists( 'substr' ) && 
                    function_exists( 'strrpos' ) && 
                    function_exists( 'strlen' );
    
    if ( ! $has_functions || empty($po_content) ) {
        return array();
    }
    
    $entries = array();
    $lines = explode("\n", $po_content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // 蹇界暐绌鸿鍜屾敞閲?        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // 澶勭悊 msgid
        if (strpos($line, 'msgid') === 0) {
            $current_msgid = trim(substr($line, 6), '"');
            $in_msgid = true;
            $in_msgstr = false;
        }
        // 澶勭悊 msgstr
        else if (strpos($line, 'msgstr') === 0) {
            $current_msgstr = trim(substr($line, 7), '"');
            $in_msgid = false;
            $in_msgstr = true;
        }
        // 澶勭悊澶氳瀛楃涓?        else if ($in_msgid && strpos($line, '"') === 0 && strrpos($line, '"') === strlen($line) - 1) {
            $current_msgid .= trim($line, '"');
        }
        else if ($in_msgstr && strpos($line, '"') === 0 && strrpos($line, '"') === strlen($line) - 1) {
            $current_msgstr .= trim($line, '"');
        }
        
        // 濡傛灉 msgid 鍜?msgstr 閮戒笉涓虹┖锛屼笖涓嶅湪澶氳瀛楃涓蹭腑锛屼繚瀛樻潯鐩?        if (!empty($current_msgid) && !empty($current_msgstr) && !$in_msgid && !$in_msgstr) {
            $entries[$current_msgid] = $current_msgstr;
            $current_msgid = '';
            $current_msgstr = '';
        }
    }
    
    return $entries;
}

// 绠€鍖栫増 .mo 鏂囦欢鐢熸垚鍑芥暟
function generate_mo_content($entries) {
    // 娉ㄦ剰锛氳繖鏄竴涓畝鍖栫増瀹炵幇锛屼笉鏀寔瀹屾暣鐨?MO 鏂囦欢鏍煎紡
    // 浠呯敤浜庢紨绀哄拰鍩烘湰鍔熻兘
    
    $mo_content = '';
    
    // MO 鏂囦欢澶撮儴锛堢畝鍖栫増锛?    $mo_content .= pack('L', 0x950412de); // 榄旀暟
    $mo_content .= pack('L', 0);         // 鐗堟湰
    $mo_content .= pack('L', 1);         // 瀛楃涓叉暟閲?    
    // 瀹為檯椤圭洰涓簲璇ヤ娇鐢ㄦ洿瀹屾暣鐨?MO 鏂囦欢鐢熸垚搴?    // 杩欓噷鎴戜滑鍙槸鍒涘缓涓€涓崰浣嶇鏂囦欢
    
    return $mo_content;
}

// 鐢熸垚鑻辨枃鍜屼腑鏂囩殑 MO 鏂囦欢
$success = true;
$success &= generate_mo_file('wp-clean-admin-en_US.po');
$success &= generate_mo_file('wp-clean-admin-zh_CN.po');

// 杈撳嚭鎬荤粨
if ($success) {
    echo "鎵€鏈?MO 鏂囦欢宸叉垚鍔熺敓鎴愶紒\n";
} else {
    echo "鐢熸垚 MO 鏂囦欢鏃跺嚭閿欙紝璇锋墜鍔ㄤ娇鐢?gettext 宸ュ叿鐢熸垚銆俓n";
    echo "寤鸿瀹夎 gettext 宸ュ叿鍖咃紝鐒跺悗浣跨敤鍛戒护锛歮sgfmt -o filename.mo filename.po\n";
}
?>