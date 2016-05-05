<?php

/**
 * SQL模板查询操作类
 * Created by PhpStorm.
 * User: WYC
 * Date: 2016-05-04
 * Time: 17:14
 */
namespace common\components\sqltemplate;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\caching\Cache;
use yii\di\Instance;

class SqlMap extends Component{

    public $templatePath = '@app/sqltemplate';

    public static $sqlNodeId = "id";
    public static $tabPrefixReplace = '{pre}';

    public $cache='cache';
    public $cacheKey='sqlmap';


    public function init()
    {
        parent::init();

        $this->templatePath=Yii::getAlias($this->templatePath);

        if(!isset($this->templatePath)||empty($this->templatePath)){
            throw new InvalidConfigException('SqlMap config templatePath invalid.');
        }
        if(!file_exists($this->templatePath)){
            throw new Exception('SqlMap templatePath does not exist.');
        }
        if ($this->cache !== null) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        }
        if($this->cache==null){
            throw new Exception('SqlMap cache does not init success.');
        }
        if($this->needLoad()){
            $arr=$this->loadXmlFile();
            $this->cacheData($arr);
        }
    }

    public function cacheData($arr=[]){
        $this->cache->delete($this->cacheKey);
        $data=new SqlMapData(time(),$arr);
        $this->cache->set($this->cacheKey,$data);
    }

    public function getSql($id=''){
        $data=$this->cache->get($this->cacheKey);
        $arr=$data->getCacheData();
        if(isset($arr[$id])){
            //---替换表前缀
            return str_replace(self::$tabPrefixReplace,Yii::$app->db->tablePrefix,$arr[$id]);
        }else{
            throw new Exception('SqlMap sqltemplate sqlid '.$id.' not found.');
        }
    }

    private function needLoad(){
        if(YII_DEBUG){
            return true;
        }else{
            $data=$this->cache->get($this->cacheKey);
            if ($data=== false) {
                return true;
            }else{
                $cacheTime=$data->getCacheTime();
                $arr=$data->getCacheData();
                $fileNames=array();
                $this->get_all_files($this->templatePath,$fileNames);
                if(count($fileNames)!=count($arr)){
                    return true;
                }
                foreach($fileNames as $fileName){
                    if(filemtime($fileName)>$cacheTime){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function loadXmlFile()
    {
        $array = array();
        $dom = new \DOMDocument();

        $fileNames=array();
        $this->get_all_files($this->templatePath,$fileNames);

        foreach($fileNames as $fileName){
            $dom->load($fileName);
            $nodes=$dom->documentElement->childNodes;
            foreach($nodes as $node){
                if ($node->nodeType != XML_TEXT_NODE) {
                    foreach ($node->attributes as $attr) {
                        if($attr->nodeName==self::$sqlNodeId){
                            if(!isset($array[$attr->nodeValue])){
                                $array[$attr->nodeValue]=$node->nodeValue;
                            }else{
                                throw new Exception('DuplicateException sqltemplate sqlNodeId "'.$attr->nodeValue.'" is duplicated.');
                            }
                        }
                    }
                }
            }
        }
        return $array;
    }

    private function get_all_files($path,&$files) {
        if(is_dir($path)){
            $dp = dir($path);
            while ($file = $dp ->read()){
                if($file !="." && $file !=".."){
                    $this->get_all_files($path."/".$file, $files);
                }
            }
            $dp ->close();
        }
        if(is_file($path)){
            //substr($path, strrpos($path, '.'));
            if(strtoupper(pathinfo($path)['extension'])=='XML'){
                $files[] =  $path;
            }
        }
    }


}