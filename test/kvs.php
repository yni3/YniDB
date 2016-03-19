<?php

require "SplClassLoader.php";

use YniDB\SQLite\SQLite;

$classLoader = new SplClassLoader('YniDB', '../vendor');
$classLoader->register();

class KVSTest extends PHPUnit_Framework_TestCase {
    
    private $dummy_datas = array();
    private $kvs;
    
    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        for($i = 0;$i < 1000;$i++){
            $this->dummy_datas[md5($i)] = md5($i);
        }
        $this->kvs = YniDB\YniKVS::createSQLite(SQLite::SQLITE_MEMORY_MODE);
        foreach ($this->dummy_datas as $key => $value) {
            $this->kvs->set($key, $value);
        }
    }
    
    const FIRST = 1;
    const SECOND = 1;
    const THIRD = 1;
    
    public function testBasic() {        
        $this->kvs->set(md5(1), "bb");
        $this->assertEquals($this->kvs->get(md5(1)),"bb");
        $this->kvs->set(md5(2), "gg");
        $this->assertEquals($this->kvs->get(md5(2)),"gg");
        
        $this->kvs->set(md5(1), array("XXX"));
        $this->assertEquals($this->kvs->get(md5(1)),array("XXX"));
        
        $this->kvs->delete(md5(10));
        $this->kvs->delete(md5(500));
        $this->assertEquals($this->kvs->get(md5(10)),NULL);
        $this->assertEquals($this->kvs->get(md5(500)),NULL);
        
        $this->kvs->set(md5(1001),md5(1001));
        $this->kvs->set(md5(1002),md5(1002));
    }
    
    public function testSequensial() {
        $cnt = $this->kvs->count();
        $this->assertEquals($cnt,1000);
        $checked = array();
        for($i = 0;$i < $cnt;$i++){
            $val = $this->kvs->getSequential($i);
            $key = $this->kvs->getSequentialKey($i);
            //空であるはずない
            $this->assertNotEmpty($val);
            $this->assertNotEmpty($key);
            switch($key){
                case md5(2):
                    $this->assertEquals($val,"gg");
                    break;
                case md5(1):
                    $this->assertEquals($val,array("XXX"));
                    break;
                default:
                    //重複したものが取り出されていないか？
                    $this->assertArrayNotHasKey($val, $checked);
                    $checked[$val] = false;
                    //キー名と同じはず
                    $this->assertEquals($val,$key);
                    break;
            }            
        }
    }
    
    public function testSequensial2() {
        $cnt = 0;
        $checked = array();
        foreach($this->kvs AS $key => $val){
            //空であるはずない
            $this->assertNotEmpty($val);
            $this->assertNotEmpty($key);
            switch($key){
                case md5(2):
                    $this->assertEquals($val,"gg");
                    break;
                case md5(1):
                    $this->assertEquals($val,array("XXX"));
                    break;
                default:
                    //重複したものが取り出されていないか？
                    $this->assertArrayNotHasKey($val, $checked);
                    $checked[$val] = false;
                    //キー名と同じはず
                    $this->assertEquals($val,$key);
                    break;
            }
            $cnt++;
        }
        //こちらの方法でも1000数えている。
        $this->assertEquals($cnt,1000);
    }

}
