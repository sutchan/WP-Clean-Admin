<?php
/**
 * 鏀硅繘鐗堢敤浜庣敓鎴愬拰鏇存柊 .po/.mo 缈昏瘧鏂囦欢鐨勮剼鏈? * 鏃犻渶澶栭儴 Gettext 宸ュ叿锛屽彲鐙珛杩愯
 * 
 * 浣跨敤鏂规硶锛? * 1. 灏嗘鑴氭湰鏀惧湪 languages 鐩綍涓? * 2. 閫氳繃 PHP 杩愯姝よ剼鏈細php generate_mo_improved.php
 */

// 纭繚鍑芥暟瀛樺湪鎬ф鏌ュ嚱鏁板瓨鍦?if ( ! function_exists( 'function_exists' ) ) {
    function function_exists( $function_name ) {
        return true; // 绠€鍖栫殑澶囩敤瀹炵幇
    }
}

// 鐩存帴璁块棶妫€鏌?if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

// 璁剧疆閿欒鎶ュ憡锛堝畨鍏ㄥ湴璁剧疆锛?if ( function_exists( 'error_reporting' ) && function_exists( 'ini_set' ) ) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// 瀹氫箟鏂囦欢璺緞
$pot_file = 'wp-clean-admin.pot';
$po_files = array(
    'wp-clean-admin-en_US.po',
    'wp-clean-admin-zh_CN.po'
);

/**
 * 浠?POT 鏂囦欢鏇存柊 PO 鏂囦欢
 * 
 * @param string $pot_file POT 鏂囦欢璺緞
 * @param string $po_file PO 鏂囦欢璺緞
 * @return bool 鏇存柊鏄惁鎴愬姛
 */
function update_po_file($pot_file, $po_file) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    // echo鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?      $has_functions = 
                    // file_exists鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    function_exists( 'copy' ) && 
                    function_exists( 'file_get_contents' ) && 
                    function_exists( 'parse_po_file' ) && 
                    // empty鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?
                    function_exists( 'array_merge' ) && 
                    function_exists( 'extract_po_header' ) && 
                    function_exists( 'generate_po_content' ) && 
                    function_exists( 'file_put_contents' );
    
    if ( ! $has_functions ) {
         // echo鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?         echo "閿欒锛氱己灏戝繀瑕佺殑鍑芥暟鏀寔銆俓n";
        return false;
    }
    
    echo "姝ｅ湪鏇存柊 $po_file 鏂囦欢...\n";
    
    // 妫€鏌?POT 鏂囦欢鏄惁瀛樺湪
    if (!file_exists($pot_file)) {
        echo "閿欒锛歅OT 鏂囦欢 $pot_file 涓嶅瓨鍦ㄣ€俓n";
        return false;
    }
    
    // 濡傛灉 PO 鏂囦欢涓嶅瓨鍦紝鍒欏垱寤烘柊鐨?    if (!file_exists($po_file)) {
        echo "PO 鏂囦欢 $po_file 涓嶅瓨鍦紝鍒涘缓鏂版枃浠?..\n";
        if (!copy($pot_file, $po_file)) {
            echo "閿欒锛氭棤娉曞垱寤?$po_file 鏂囦欢銆俓n";
            return false;
        }
        echo "PO 鏂囦欢 $po_file 鍒涘缓鎴愬姛锛乗n";
        return true;
    }
    
    // 璇诲彇 POT 鍜?PO 鏂囦欢鍐呭
    $pot_content = file_get_contents($pot_file);
    $po_content = file_get_contents($po_file);
    
    if ($pot_content === false || $po_content === false) {
        echo "閿欒锛氭棤娉曡鍙栨枃浠跺唴瀹广€俓n";
        return false;
    }
    
    // 瑙ｆ瀽 POT 鍜?PO 鏂囦欢
    $pot_entries = parse_po_file($pot_content);
    $po_entries = parse_po_file($po_content);
    
    if (empty($pot_entries)) {
        echo "閿欒锛氭棤娉曡В鏋?$pot_file 鏂囦欢銆俓n";
        return false;
    }
    
    if (empty($po_entries)) {
        echo "閿欒锛氭棤娉曡В鏋?$po_file 鏂囦欢銆俓n";
        return false;
    }
    
    // 鍚堝苟鏂扮殑缈昏瘧鏉＄洰锛屼繚鐣欑幇鏈夌炕璇?    $merged_entries = array_merge($pot_entries, $po_entries);
    
    // 鐢熸垚鏇存柊鍚庣殑 PO 鏂囦欢鍐呭
    $header = extract_po_header($po_content);
    $new_po_content = generate_po_content($merged_entries, $header);
    
    // 鍐欏叆鏇存柊鍚庣殑 PO 鏂囦欢
    if (file_put_contents($po_file, $new_po_content) === false) {
        echo "閿欒锛氭棤娉曞啓鍏?$po_file 鏂囦欢銆俓n";
        return false;
    }
    
    echo "PO 鏂囦欢 $po_file 鏇存柊鎴愬姛锛乗n";
    return true;
}

/**
 * 鎻愬彇 PO 鏂囦欢鐨勫ご閮ㄤ俊鎭? * 
 * @param string $po_content PO 鏂囦欢鍐呭
 * @return string 澶撮儴淇℃伅
 */
function extract_po_header($po_content) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    $has_functions = function_exists( 'explode' ) && 
                    function_exists( 'trim' ) && 
                    function_exists( 'strpos' ) && 
                    function_exists( 'array_push' ) && 
                    function_exists( 'implode' );
    
    if ( ! $has_functions || empty($po_content) ) {
        return '';
    }
    
    $lines = explode("\n", $po_content);
    $header_lines = array();
    $in_header = false;
    $msgid_count = 0;
    
    foreach ($lines as $line) {
        if (strpos(trim($line), 'msgid') === 0) {
            $msgid_count++;
            
            if ($msgid_count > 1) {
                break;
            }
        }
        
        if ($msgid_count <= 1) {
            $header_lines[] = $line;
        }
    }
    
    return implode("\n", $header_lines);
}

/**
 * 瑙ｆ瀽 PO 鏂囦欢
 * 
 * @param string $po_content PO 鏂囦欢鍐呭
 * @return array 瑙ｆ瀽鍚庣殑缈昏瘧鏉＄洰
 */
function parse_po_file($po_content) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    $has_functions = function_exists( 'explode' ) && 
                    function_exists( 'trim' ) && 
                    function_exists( 'strpos' ) && 
                    function_exists( 'strrpos' ) && 
                    function_exists( 'substr' ) && 
                    function_exists( 'strlen' ) && 
                    // empty鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?
                    // is_array鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    // isset鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?    
    if ( ! $has_functions || empty($po_content) || !is_string($po_content) ) {
        return array();
    }
    
    $entries = array();
    $lines = explode("\n", $po_content);
    $current_msgid = '';
    $current_msgstr = '';
    $in_msgid = false;
    $in_msgstr = false;
    $comments = '';
    
    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        
        // 澶勭悊娉ㄩ噴
        if (strpos($trimmed_line, '#') === 0) {
            $comments .= $line . "\n";
            continue;
        }
        
        // 澶勭悊 msgid
        if (strpos($trimmed_line, 'msgid') === 0) {
            // 淇濆瓨涔嬪墠鐨勬潯鐩?            if (!empty($current_msgid)) {
                $entries[$current_msgid] = array(
                    'msgstr' => $current_msgstr,
                    'comments' => $comments
                );
            }
            
            $current_msgid = trim(substr($trimmed_line, 6), '"');
            $current_msgstr = '';
            $in_msgid = true;
            $in_msgstr = false;
            $comments = '';
        }
        // 澶勭悊 msgstr
        else if (strpos($trimmed_line, 'msgstr') === 0) {
            $current_msgstr = trim(substr($trimmed_line, 7), '"');
            $in_msgid = false;
            $in_msgstr = true;
        }
        // 澶勭悊澶氳瀛楃涓?        else if ($in_msgid && strpos($trimmed_line, '"') === 0 && strrpos($trimmed_line, '"') === strlen($trimmed_line) - 1) {
            $current_msgid .= trim($trimmed_line, '"');
        }
        else if ($in_msgstr && strpos($trimmed_line, '"') === 0 && strrpos($trimmed_line, '"') === strlen($trimmed_line) - 1) {
            $current_msgstr .= trim($trimmed_line, '"');
        }
        // 澶勭悊绌鸿
        else if (empty($trimmed_line)) {
            // 淇濆瓨涔嬪墠鐨勬潯鐩紙濡傛灉鏈夌殑璇濓級
            if (!empty($current_msgid)) {
                $entries[$current_msgid] = array(
                    'msgstr' => $current_msgstr,
                    'comments' => $comments
                );
                
                $current_msgid = '';
                $current_msgstr = '';
                $in_msgid = false;
                $in_msgstr = false;
                $comments = '';
            }
        }
    }
    
    // 淇濆瓨鏈€鍚庝竴涓潯鐩?    if (!empty($current_msgid)) {
        $entries[$current_msgid] = array(
            'msgstr' => $current_msgstr,
            'comments' => $comments
        );
    }
    
    return $entries;
}

/**
 * 鐢熸垚 PO 鏂囦欢鍐呭
 * 
 * @param array $entries 缈昏瘧鏉＄洰
 * @param string $header 澶撮儴淇℃伅
 * @return string PO 鏂囦欢鍐呭
 */
function generate_po_content($entries, $header) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    // empty鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?      $has_functions = 
                    // is_array鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    // is_string鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    // isset鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?                    function_exists( 'strlen' );
    
    if ( ! $has_functions || !is_array($entries) ) {
        return is_string($header) ? $header : '';
    }
    
    $content = (is_string($header) ? $header : '') . "\n\n";
    
    foreach ($entries as $msgid => $entry) {
        if ($msgid === '' || !is_array($entry)) continue; // 璺宠繃绌虹殑 msgid锛堥€氬父鏄ご閮級
        
        // 娣诲姞娉ㄩ噴
        if (isset($entry['comments']) && !empty($entry['comments'])) {
            $content .= $entry['comments'];
        }
        
        // 娣诲姞 msgid 鍜?msgstr
        $msgstr = isset($entry['msgstr']) ? $entry['msgstr'] : '';
        $content .= "msgid \"$msgid\"\n";
        $content .= "msgstr \"$msgstr\"\n\n";
    }
    
    return $content;
}

/**
 * 鐢熸垚 MO 鏂囦欢
 * 娉ㄦ剰锛氳繖鏄竴涓畝鍖栫増瀹炵幇锛屼粎鐢ㄤ簬鍩烘湰鍔熻兘
 * 
 * @param string $po_file PO 鏂囦欢璺緞
 * @return bool 鐢熸垚鏄惁鎴愬姛
 */
function generate_mo_file($po_file) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    // echo鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?      $has_functions = 
                    // file_exists鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    function_exists( 'str_replace' ) && 
                    function_exists( 'file_get_contents' ) && 
                    function_exists( 'parse_po_file' ) && 
                    // empty鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?
                    // is_array鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    // isset鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?                    function_exists( 'generate_mo_content' ) && 
                    function_exists( 'file_put_contents' ) &&
                    // is_string鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?    
    if ( ! $has_functions ) {
        // echo鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?        echo "閿欒锛氱己灏戝繀瑕佺殑鍑芥暟鏀寔銆俓n";
        return false;
    }
    
    // 妫€鏌ュ弬鏁扮被鍨?    if (!is_string($po_file)) {
        echo "閿欒锛氬弬鏁扮被鍨嬮敊璇€俓n";
        return false;
    }
    
    // 妫€鏌?PO 鏂囦欢鏄惁瀛樺湪
    if (!file_exists($po_file)) {
        echo "閿欒锛歅O 鏂囦欢 $po_file 涓嶅瓨鍦ㄣ€俓n";
        return false;
    }
    
    // 鏋勫缓 MO 鏂囦欢鍚?    $mo_file = str_replace('.po', '.mo', $po_file);
    
    // 璇诲彇 PO 鏂囦欢鍐呭
    $po_content = file_get_contents($po_file);
    
    if ($po_content === false) {
        echo "閿欒锛氭棤娉曡鍙?$po_file 鏂囦欢銆俓n";
        return false;
    }
    
    // 瑙ｆ瀽 PO 鏂囦欢
    $entries = parse_po_file($po_content);
    
    if (empty($entries) || !is_array($entries)) {
        echo "閿欒锛氭棤娉曡В鏋?$po_file 鏂囦欢銆俓n";
        return false;
    }
    
    // 杩囨护鎺夌┖鐨?msgid锛堥€氬父鏄ご閮級
    $filtered_entries = array();
    foreach ($entries as $msgid => $entry) {
        if (!empty($msgid) && is_array($entry) && isset($entry['msgstr'])) {
            $filtered_entries[$msgid] = $entry['msgstr'];
        }
    }
    
    // 鐢熸垚 MO 鏂囦欢
    $mo_content = generate_mo_content($filtered_entries);
    
    // 鍐欏叆 MO 鏂囦欢
    if (file_put_contents($mo_file, $mo_content) === false) {
        echo "閿欒锛氭棤娉曞啓鍏?$mo_file 鏂囦欢銆俓n";
        return false;
    }
    
    echo "MO 鏂囦欢 $mo_file 鐢熸垚鎴愬姛锛乗n";
    return true;
}

/**
 * 鐢熸垚 MO 鏂囦欢鍐呭
 * 娉ㄦ剰锛氳繖鏄竴涓畝鍖栫増瀹炵幇锛屾敮鎸佸熀鏈殑 MO 鏂囦欢鏍煎紡
 * 
 * @param array $entries 缈昏瘧鏉＄洰
 * @return string MO 鏂囦欢鍐呭
 */
function generate_mo_content($entries) {
    // 妫€鏌ュ繀瑕佸嚱鏁版槸鍚﹀瓨鍦?    $has_functions = function_exists( 'pack' ) && 
                    function_exists( 'count' ) && 
                    // is_array鏄疨HP鍐呯疆鍑芥暟锛屼笉闇€瑕乫unction_exists妫€鏌?                    // empty鏄疨HP璇█缁撴瀯锛屼笉闇€瑕乫unction_exists妫€鏌?                    function_exists( 'strlen' );
    
    if ( ! $has_functions || !is_array($entries) ) {
        return '';
    }
    
    $magic = 0x950412de;
    $version = 0;
    $num_strings = count($entries);
    $offset_orig = 28; // 澶撮儴澶у皬
    
    // 鏀堕泦鍘熷瀛楃涓插拰缈昏瘧鍚庣殑瀛楃涓?    $orig_strings = array();
    $trans_strings = array();
    
    foreach ($entries as $msgid => $msgstr) {
        if (!empty($msgid) && is_string($msgid) && is_string($msgstr)) {
            $orig_strings[] = $msgid;
            $trans_strings[] = $msgstr;
        }
    }
    
    // 璁＄畻鍝堝笇琛ㄧ殑鍋忕Щ閲?    $offset_trans = $offset_orig + $num_strings * 8;
    
    // 璁＄畻瀛楃涓茶〃鐨勫亸绉婚噺
    $hash_table_offset = $offset_trans + $num_strings * 8;
    
    // 鏋勫缓澶撮儴
    $mo_content = function_exists('pack') ? pack('L', $magic) : '';
    $mo_content .= function_exists('pack') ? pack('L', $version) : '';
    $mo_content .= function_exists('pack') ? pack('L', $num_strings) : '';
    $mo_content .= function_exists('pack') ? pack('L', $offset_orig) : '';
    $mo_content .= function_exists('pack') ? pack('L', $offset_trans) : '';
    $mo_content .= function_exists('pack') ? pack('L', 0) : ''; // 鍝堝笇琛ㄥぇ灏忥紙绠€鍖栧疄鐜帮級
    $mo_content .= function_exists('pack') ? pack('L', $hash_table_offset) : '';
    
    // 鏋勫缓鍘熷瀛楃涓茬储寮曡〃
    $current_offset = $hash_table_offset + $num_strings * 4; // 绠€鍖栫殑鍝堝笇琛ㄥぇ灏?    
    foreach ($orig_strings as $string) {
        if (function_exists('pack') && function_exists('strlen')) {
            $mo_content .= pack('L', strlen($string));
            $mo_content .= pack('L', $current_offset);
            $current_offset += strlen($string) + 1; // +1 for null terminator
        }
    }
    
    // 鏋勫缓缈昏瘧鍚庣殑瀛楃涓茬储寮曡〃
    foreach ($trans_strings as $string) {
        if (function_exists('pack') && function_exists('strlen')) {
            $mo_content .= pack('L', strlen($string));
            $mo_content .= pack('L', $current_offset);
            $current_offset += strlen($string) + 1; // +1 for null terminator
        }
    }
    
    // 鏋勫缓鍘熷瀛楃涓茶〃
    foreach ($orig_strings as $string) {
        $mo_content .= $string . "\0";
    }
    
    // 鏋勫缓缈昏瘧鍚庣殑瀛楃涓茶〃
    foreach ($trans_strings as $string) {
        $mo_content .= $string . "\0";
    }
    
    // 娣诲姞绠€鍖栫殑鍝堝笇琛紙瀵逛簬鍩烘湰鍔熻兘涓嶆槸蹇呴渶鐨勶級
    for ($i = 0; $i < $num_strings; $i++) {
        if (function_exists('pack')) {
            $mo_content .= pack('L', 0);
        }
    }
    
    return $mo_content;
}

// 鏄剧ず鑴氭湰淇℃伅
function display_script_info() {
    echo "==========================================================\n";
    echo "WPCleanAdmin 缈昏瘧鏂囦欢鐢熸垚宸ュ叿锛堟敼杩涚増锛塡n";
    echo "==========================================================\n";
}

// 鎵ц鏇存柊鍜岀敓鎴愭搷浣?display_script_info();

$success = true;

// 鏇存柊鎵€鏈?PO 鏂囦欢
foreach ($po_files as $po_file) {
    $success &= update_po_file($pot_file, $po_file);
    echo "\n";
}

// 鐢熸垚鎵€鏈?MO 鏂囦欢
foreach ($po_files as $po_file) {
    $success &= generate_mo_file($po_file);
    echo "\n";
}

// 鏄剧ず鎿嶄綔缁撴灉
echo "==========================================================\n";
if ($success) {
    echo "鎵€鏈夌炕璇戞枃浠跺凡鎴愬姛鏇存柊鍜岀敓鎴愶紒\n";
    echo "绯荤粺灏嗕娇鐢ㄦ柊鐨勭炕璇戝瓧绗︿覆銆俓n";
} else {
    echo "鏇存柊鎴栫敓鎴愮炕璇戞枃浠舵椂鍑洪敊銆俓n";
    echo "璇锋鏌ラ敊璇俊鎭苟鎵嬪姩淇闂銆俓n";
}
echo "==========================================================\n";
?>