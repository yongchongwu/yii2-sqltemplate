<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-05-04
 * Time: 17:30
 */

namespace common\components\sqltemplate;

use yii\base\Object;

class SqlMapData extends Object
{
    private $cacheTime;
    private $cacheData=[];

    /**
     * SqlMapData constructor.
     * @param $cacheTime
     * @param array $cacheData
     */
    public function __construct($cacheTime, array $cacheData)
    {
        $this->cacheTime = $cacheTime;
        $this->cacheData = $cacheData;
    }

    /**
     * @return mixed
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * @param mixed $cacheTime
     */
    public function setCacheTime($cacheTime)
    {
        $this->cacheTime = $cacheTime;
    }

    /**
     * @return array
     */
    public function getCacheData()
    {
        return $this->cacheData;
    }

    /**
     * @param array $cacheData
     */
    public function setCacheData($cacheData)
    {
        $this->cacheData = $cacheData;
    }
}