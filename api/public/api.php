<?php
// 環境
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

require_once 'Zend/Rest/Server.php';
require_once 'Zend/Config/Ini.php';
require_once '../model/kv.class.php';
require_once '../model/pair.class.php';
require_once '../model/Category.class.php';
require_once '../model/wantToEatCategory.class.php';
require_once 'Zend/Oauth/Token/Access.php';
require_once 'Zend/Service/Twitter.php';
require_once 'Zend/Db/Expr.php';

class HappyDinnerAPI
{
    public function __construct()
    {
        // DB
        $config = new Zend_Config_Ini('../configs/application.ini', APPLICATION_ENV);
        $db = Zend_Db::factory($config->database);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    public function getkvs($key){
        $kv = new KV();
        $select = $kv->select()->where('`key` = ?', $key);
        $row = $kv->fetchrow($select);
        return array('value' => $row->value);
    }

    public function setkvs($key, $value){
        $kv = new KV;
        try {
            $kv->insert(array('key' => $key, 'value' => $value));
        } catch ( Exception $e ){
            // insert 失敗時は update することで upsert を実現
            $where = $kv->getAdapter()->quoteInto('`key` = ?', $key);
            $kv->update(array('value' => $value), $where);
        }
    }

    public function registerPair($fbId, $partnerId){
        $pair = new Pair;

        try {
             $pair->insert(array('fb_id' => $fbId, 'partner_id' => $partnerId));
        } catch ( Exception $e ){
            // @todo 二重登録されたとき: ハンドリングせずにupdate
            $where = $pair->getAdapter()->quoteInto('`fb_id` = ?', $fbId);
            $pair->update(array('partner_id' => $partnerId), $where);
        }
    }

    public function setCategory($fbId, $categoryId){
        $category = new wantToEatCategory();

        try {
            $category->insert(array('date' => (new Zend_Db_Expr('current_date')), 'fb_id' => $fbId, 'category_id' => $categoryId));
        } catch ( Exception $e ){
            // @todo 二重登録されたとき: ハンドリングせずにupdate
            // @todo 主キーもっとある
            $where = $category->getAdapter()->quoteInto('`fb_id` = ? AND date = current_date ', $fbId);
            $category->update(array('category_id' => $categoryId), $where);
        }
    }
}

$server = new Zend_Rest_Server();
$server->setClass('HappyDinnerAPI');
$server->handle();
