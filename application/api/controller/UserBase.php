<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 2018/1/19
 * Time: 14:31
 * 用于登陆验证等
 */
namespace app\api\controller;
use think\facade\Cache;
use think\Controller;
class UserBase extends Controller{
/*    public function __construct()
    {
        parent::__construct();
        echo "base controller";
        $aa= Cache::get($_REQUEST['token']);
        echo 'test if token set'.$aa;
        if(!isset($_REQUEST['token'])||!Cache::get($_REQUEST['token'])){
            echo "beybey";
            return json(array('code'=>'0'));
            exit();
        }
    }*/
    
    public function login(){
        $user_id = $_REQUEST['user_id'];
        $array=array('id'=>$user_id,'nickname=>hellokiki');
        $token = sha1(time());
        echo $token;
        Cache::set($token,$array,3600*7*24*10000);
        Cache::set('little',array('id'=>'little_star','nickname'=>'yishang'),3600*7*24*10000);
        //echo 'login 方法';
    }
}