<?php
namespace Model;
 
trait Tool {
    
    protected $ci;
    protected $db;
 
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->database();
        $this->db = $this->ci->db;
    }
 
    /**
     * 返回資料庫取得的樣式
     * @param   $query       查詢結果資源
     * @param   $info_list   單筆資料 info | 多筆資料 list
     * @return               當列數為 0 時返回 "false" | 筆數 > 0 返回 "二維列表"
     */
    protected function result($query, $info_list)
    {
        if (!in_array($info_list, ['info', 'list'])) throw new \Exception("SQL 查詢結果請指定回傳 info | list");
        
        // 可返回字串
        if (is_string($query)) return $query;
 
        $result = $query->result_array(); // 查詢結果
        $num    = count($result); //列數
 
        // 如果數量是 0 那返回 false
        if ($num === 0) return false;
        
        // 若有資料，把二維陣列都改採 \Jsnlib 方式
        $result = new \Jsnlib\Ao($result);
        if ($info_list == "info") return $result[0];
        else return $result;
    }
}