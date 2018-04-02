<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 2018/1/19
 * Time: 14:29
 */
namespace app\api\controller;
//use app\api\UserBase;
use think\Controller;
use think\facade\Cache;
use think\Db;
class User extends UserBase{

    public function save_run_test(){
        $send = json_decode(file_get_contents("php://input"));
        if(!Cache::get($send->token)){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok=  Cache::get($send->token);
        $data['user_id']= $tok['id'];
        //dump($send);
        $data['distance']= $send->distance;
        $data['speed']=$send->speed;
        $start_time = $send->start_time;
        $end_time = $send->end_time;
        $data['start_time']=strtotime($start_time);
        $data['end_time']=strtotime($end_time);
        $data['last_time'] = $send->last_time;
        $data['calorie']=$send->calorie;
        $data['latitude'] = $send->location->latitude;
        $data['longitude'] = $send->location->longitude;
        $data['note_content'] = $send->note->tips;
        $data['note_latitude'] = $send->note->latitude;
        $data['note_longitude'] = $send->note->longitude;


        $data['create_time']=time();
        $res = db('run_record')->insert($data);
        if($res){
            return json(array('code'=>1,'msg'=>'success'));
        }
        else{
            return json(array('code'=>0,'msg'=>'error'));
        }
        //$location = $send->localtion;

       // $data['longitude'] = $location->longitude;
        //$data['latitude'] = $location->latitude;
 /*       $data['note_content'] = $note->tips;
        $data['note_longitude'] = $note->longitude;
        $data['note_latitude'] = $note->latitude;*/
        //dump($data);

    }

    public function test_save_array(){
        $send = json_decode(file_get_contents("php://input"));
        $array = $send->trace;
        var_dump($array);
        $name = './uploads/trace/'.time().'.txt';
//        $arr = var_export($array,"true");
        //$fp = fopen("$name", "w");
        //fwrite($fp,serialize($array));
        //fclose($fp);
       // $gf = fopen($name,"r" );
        //dump($arr);
        file_put_contents("$name",serialize($array));
        $handle = file_get_contents($name);
        
        $res = unserialize($handle);
        var_dump($res);
        //file_put_contents($name,serialize($array) );
        return 1;
    }

    public function run_note(){
        if(!Cache::get($_POST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok=  Cache::get($_POST['token']);
        $user_id = $tok['id'];
        $run_id = $_POST['run_id'];
        $longitude = $_POST['longitude'];
        $latitude = $_POST['latitude'];
        $note = $_POST['note'];
        Db::name('run_record')->where('id',$run_id)->setField(array('longitude'=>$longitude,'latitude'=>$latitude,'note_content'=>$note));
    }
    public function save_run(){//保存用户跑步记录
        $sen = $_REQUEST['run'];//file_get_contents("php://input");
        //$new = json_decode($sen,true);
        //var_dump($new);
        //echo $new->token;
        var_dump($sen);
        $send = json_decode($sen, true);
        var_dump($send);
        if(!Cache::get($send['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok=  Cache::get($send['token']);
        $data['user_id']= $tok['id'];
        //dump($send);
        $data['distance']= $send['distance'];
        $data['speed']=$send['speed'];
        $start_time = $send['start_time'];
        $end_time = $send['end_time'];
        $data['start_time']=$start_time;
        $data['end_time']=$end_time;
        $data['last_time'] = $send['last_time'];
        $data['calorie']=$send['calorie'];
        $nutrient = floor($data['distance']/2);
        $old = Db::name('user')->where('id',$data['user_id'])->value('nutrient');
        Db::name('user')->where('id',$data['user_id'])->setField('nutrient',$old+$nutrient);
        $trace = $send['trace'];
        $name = './uploads/trace/'.time().'.txt';
        file_put_contents("$name",serialize($trace));
        //$handle = file_get_contents($name);
        $data['trace'] = $name;
        //$res = unserialize($handle);
        //var_dump($res);
        //var_dump($trace);
        $data['create_time']=time();
        $res = db('run_record')->insert($data);
        if($res){
            return json(array('code'=>1,'msg'=>'success'));
        }
        else{
            return json(array('code'=>0,'msg'=>'error'));
        }

    }

    public function run_record(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $run = Db::name('run_record')
            ->where('user_id',$user_id)
            ->order('create_time desc')
            ->field('id,user_id,distance,calorie,speed,note_content, trace, last_time,start_time,end_time,longitude,latitude')
            ->select();
        foreach ($run as $k=>$v){
            //        if($run[$k]['pic_url'])
            //$run[$k]['trace_url']= 'http://120.79.229.151/little_star/public/'.$v['trace_url'];
            if($v['trace']){
                $trace = unserialize(file_get_contents($v['trace']));
                $run[$k]['trace'] = $trace;
            }
            $run[$k]['start_time'] = date('h:i:s',$v['start_time']);
            $run[$k]['end_time'] = date('h:i:s',$v['end_time']);
            $run[$k]['date'] = date('Y年m月s日',$v['start_time']);
            $run_date[$k] =$run[$k]['date'];
        }
        return json($run);

    }
    public function my_run(){//“我的”运动记录
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $num = Db::name('run_record')
            ->where('user_id',$user_id)
            ->count();
        //echo $user_id;
        $run = Db::name('run_record')
            ->where('user_id',$user_id)
            ->order('create_time desc')
            ->column('id,user_id,distance,calorie,speed,note_content, trace, last_time,start_time,end_time,longitude,latitude');

        foreach ($run as $k=>$v){
    //        if($run[$k]['pic_url'])
            //$run[$k]['trace_url']= 'http://120.79.229.151/little_star/public/'.$v['trace_url'];
            if($v['trace']){
                $trace = unserialize(file_get_contents($v['trace']));
                $run[$k]['trace'] = $trace;
            }
            $run[$k]['start_time'] = date('h:i:s',$v['start_time']);
            $run[$k]['end_time'] = date('h:i:s',$v['end_time']);
            $run[$k]['date'] = date('Y年m月s日',$v['start_time']);
            $run_date[$k] =$run[$k]['date'];
        }

        $res['distance_in_all'] = Db::name('run_record')->where('user_id',$user_id)->sum('distance');
        $res['calorie_in_all'] = Db::name('run_record')->where('user_id',$user_id)->sum('calorie');
        $res['nickname'] = Db::name('user')->where('id',$user_id)->value('nickname');
        //dump($res['calorie_in_all']);
        //$res['run']=$run;
        $res['amount']=$num;
        $res['code']=1;
        $res['date_amount'] = count(array_unique($run_date));
        return json($res);

        
    }
    public function my_msg(){//我的消息
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
/*        $praise=Db::name('article_love')->where('author_id',$user_id)->field('love_user, article_id,create_time')->select();
        foreach ($praise as $k=>$v){
            $praise[$k]['author_face_url'] = Db::name('user')->where('id',$user_id)->value('pic_url');
            $praise[$k]['author'] = $v['love_user'];
            $praise['type'] = 0;
            //$praise['content'] = Db::name('article')->where('id',$v[''])
        }*/
        $res['comment'] = Db::name('comment_reply')->where('reply_to_user',$user_id)->field('id, author, reply_to, content, create_time, root')->select();
        foreach ($res['comment'] as $k=>$v){
            $res['comment'][$k]['author_face_url'] = Db::name('user')->where('id',$v['author'])->value('pic_url');
            $res['comment'][$k]['author_nickname'] = Db::name('user')->where('id',$v['author'])->value('nickname');
            $res['comment'][$k]['create_time'] = time_format($v['create_time']);

            //$res['comment']['create_time'] = $v['create_time'];
            if($v['reply_to']){//对文章的评论
                $res['comment'][$k]['type'] = 0;
                $res['comment'][$k]['content'] = Db::name('article')->where('id',$v['root'])->value('title');
            }
            else{
                $res['comment'][$k]['type'] = 1;
                $res['comment'][$k]['content'] = Db::name('comment_reply')->where('id',$v['reply_to'])->value('content');
            }
        }
        $res['code'] = 1;
        return json($res);

    }
    public function my_activity(){//我的活动

    }

    public function get_my_star(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data = Db::name('user')->where('id',$user_id)->field('nutrient,nickname')->select();
        $info['type'] = floor($data[0]['nutrient']/10)+1;
        if($info['type']>7){
            $info['type'] = 7;
        }
        $info['nutrient'] = $data[0]['nutrient']%10;
        $info['nickname'] = $data[0]['nickname'];
        return json($info);

    }

    public function get_star(){//获取星星
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $user_focus_num = Db::name('user_focus')->where('fan_id',$user_id)->count();
        if($user_focus_num>=5){
            $user_focus = Db::name('user_focus')->where('fan_id',$user_id)->column('follow_id');
            $user_focus3 = array_rand($user_focus,5);
            for($i=0;$i<5;$i++){
                $user_focus1[$i] = $user_focus[$user_focus3[$i]];
            }
        }else{
            $user_focus1 = Db::name('user_focus')->where('fan_id',$user_id)->column('follow_id');
            //var_dump($user_focus1);
            $user_focus2 = Db::name('user')->where('id','not in',$user_focus1)->limit(10)->column('id');//->limit(10)
            foreach ($user_focus2 as $k=>$v){
                if($user_id == $v) unset($user_focus2[$k]);
            }
            //var_dump($user_focus2);
            $user_focus3 = array_rand($user_focus2,5-$user_focus_num);
            $j = 0;
            for($i = $user_focus_num;$i<5;$i++){
                $user_focus1[$i] = $user_focus2[$user_focus3[$j]];
                $j++;
            }
            //$user_focus1[6] = $user_id;

            //dump($user_focus1);
            //$user_focus = array_merge()
        }


        foreach ($user_focus1 as $k=>$v){
            //echo $v;
            $data[$k]['info'] = Db::name('user')->where('id',$v)->field('nickname, nutrient')->select();
            //dump($data[$k]['info']);
            //dump($data[$k]['info'][0]['nutrient']);
            $info[$k]['type'] = floor($data[$k]['info'][0]['nutrient']/10)+1;
            $info[$k]['nutrient'] = $data[$k]['info'][0]['nutrient']%10;
            $info[$k]['nickname'] = $data[$k]['info'][0]['nickname'];
        }
        $res = array_multisort($info);
        if($res){
            $ret['code'] = 1;
            $ret['planet'] = $info;
            return json($ret);
        }
        else return json(array('code'=>0));

    }

    public function follows(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data['follows']= Db::name('user_focus')
            ->where(array('fan_id'=>$user_id))
            ->field('id,follow_id')
            ->select();
        // dump($data['follow']);
        foreach ($data['follows'] as $k=>$v){
            //dump($k);
            //dump($v);
            //dump($v['follow_id']);
            //$user_info = WebUsers::get($v['reported_user_id']);
            $follow_info = Db::name('user')->where(array('id'=>$v['follow_id']))->select();
            //dump($follow_info);

            $data['follows'][$k]['follow_id'] = $follow_info[0]['id'];
            $data['follows'][$k]['follow_nickname'] = $follow_info[0]['nickname'];
            $data['follows'][$k]['follow_sex']= $follow_info[0]['sex'];
            $data['follows'][$k]['follow_face_url']='http://120.79.229.151/little_star/public/uploads/face_img/'.$follow_info[0]['pic_url'];
        }
        return json($data);
    }

    public function fans(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data['fans']= Db::name('user_focus')
            ->where(array('follow_id'=>$user_id))
            ->field('fan_id')->select();

        foreach ($data['fans'] as $k=>$v){
            //$user_info = WebUsers::get($v['reported_user_id']);
            $fan_info = Db::name('user')->where(array('id'=>$v['fan_id']))->select();
            $data['fans'][$k]['fan_id'] = $fan_info[0]['id'];
            $data['fans'][$k]['fan_nickname'] = $fan_info[0]['nickname'];
            $data['fans'][$k]['fan_sex']= $fan_info[0]['sex'];
            $data['fans'][$k]['fan_face_url']='http://120.79.229.151/little_star/public/uploads/face_img/'.$fan_info[0]['pic_url'];
        }
        return json($data);
    }
    
    public function my_info(){//个人信息：id,昵称,关注的人总数，粉丝数，动态数
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data['info'] = Db::name('user')
            ->where('id',$user_id)
            ->find();
        $data['info']['pic_url'] = 'http://120.79.229.151/little_star/public/uploads/face_img/'.$data['info']['pic_url'];
/*        $data['my_fan']= Db::name('user_focus')
            ->where(array('follow_id'=>$user_id))
            ->field('fan_id')->select();*/
        $data['info']['activity_num'] = Db::name('article')->where('author',$user_id)->count();
        $data['info']['fans_num'] = Db::name('user_focus')->where('follow_id',$user_id)->count();
        $data['info']['follow_num'] = Db::name('user_focus')->where('fan_id',$user_id)->count();
        /*foreach ($data['my_fan'] as $k=>$v){
            //$user_info = WebUsers::get($v['reported_user_id']);
            $fan_info = Db::name('user')->where(array('id'=>$v['fan_id']))->select();
            $data['my_fan'][$k]['fan_id'] = $fan_info[0]['id'];
            $data['my_fan'][$k]['fan_nickname'] = $fan_info[0]['nickname'];
            $data['my_fan'][$k]['fan_sex']= $fan_info[0]['sex'];
            $data['my_fan'][$k]['fan_face_url']='http://120.79.229.151/little_star/public/uploads/face_img/'.$fan_info[0]['pic_url'];
        }
        $data['follow']= Db::name('user_focus')
            ->where(array('fan_id'=>$user_id))
            ->field('id,follow_id')
            ->select();
       // dump($data['follow']);
        foreach ($data['follow'] as $k=>$v){
            //dump($k);
            //dump($v);
            //dump($v['follow_id']);
            //$user_info = WebUsers::get($v['reported_user_id']);
            $follow_info = Db::name('user')->where(array('id'=>$v['follow_id']))->select();
            //dump($follow_info);

            $data['follow'][$k]['follow_id'] = $follow_info[0]['id'];
            $data['follow'][$k]['follow_nickname'] = $follow_info[0]['nickname'];
            $data['follow'][$k]['follow_sex']= $follow_info[0]['sex'];
            $data['follow'][$k]['follow_face_url']='http://120.79.229.151/little_star/public/uploads/face_img/'.$follow_info[0]['pic_url'];
        }*/
        //$data['my_follow']=$follow;
        //echo $data['follow'][1]['nickname'];

        $data['code'] = 1;
     //   dump($data);
        return json($data);
        
    }


    public function post_content(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data['author']=$user_id;
        $data['type'] = $_REQUEST['type'];
        $data['title'] = $_REQUEST['title'];
        $data['img_num'] = $_REQUEST['img_num'];
        $data['location'] = $_REQUEST['location'];
        $data['create_time']= time();

/*        if($data['img_num']==0){
            $url = 'uploads/thumbnail/1520075277.png';
            $data['thumbnail_url'] = 'uploads/thumbnail/1520075277.png';
            //Db::name('article')->where('id',$data['article_id'])->setField('thumbnail_url',$url);
        }*/

        $article_id=Db::name('article')->insertGetId($data);
        $fore_para_id = $article_id;
        $content = $_REQUEST['content'];
        foreach ($content as $k=>$v){
            $data_content['para']=$v;
            $data_content['article_id']=$article_id;
            $data_content['create_time']= time();
            $data_content['fore_para_id'] = $fore_para_id;
            $data_content['rank'] = $k;
            $fore_para_id=Db::name('article_para')->insertGetId($data_content);

        }
        return json(array('code'=>1,'article_id'=>$article_id));
    }

    public function post_img(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data['article_id'] = $_REQUEST['article_id'];
        $data['rank'] = $_REQUEST['rank'];
        $data['create_time'] = time();
        $file = request()->file('image');
        if($data['rank']==0){
            $fileee = \think\Image::open($file);
            $url = 'uploads/thumbnail/'.create_name(15).'.png';
            $fileee->thumb(150,150)->save($url);
            //$arti['thumbnail_url'] = $url;
            Db::name('article')->where('id',$data['article_id'])->setField('thumbnail_url',$url);
            //echo $url;
        }
        if($file){
            $file = request()->file('image');
            $info = $file->move('uploads/article_img');
            if($info){
                $data['pic_url'] = date('Ymd').'/'.$info->getFilename();
                Db::name('article_pic')->insert($data);
            }else{
// 上传失败获取错误信息
                return json(array('code'=>0,'msg'=>$file->geterror()));
            }
        }
        return json(array('code'=>1));
    }

    public function post(){//发布帖子或话题(完成)
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $data['author']=$user_id;
        $data['type'] = $_REQUEST['type'];
        $data['title'] = $_REQUEST['title'];
        $data['img_num'] = $_REQUEST['img_num'];
        $data['img_location'] = $_REQUEST['img_location'];
        $data['create_time']= time();
        $files = request()->file('image');
        if($files){
            $file = \think\Image::open($files[0]);
            $url = 'uploads/thumbnail/'.time().'.png';
            $file->thumb(150,150)->save($url);
            $data['thumbnail_url'] = $url;
        }
        else{
            $data['thumbnail_url'] = 'uploads/thumbnail/1520075277.png';
        }
        $article_id=Db::name('article')->insertGetId($data);
        $fore_para_id = $article_id;
        var_dump($_REQUEST['content']);

       foreach ($_REQUEST['content'] as $para){
            $data_content['para']=$para;
            $data_content['article_id']=$article_id;
            $data_content['create_time']= time();
            $data_content['fore_para_id'] = $fore_para_id;
            $fore_para_id=Db::name('article_para')->insertGetId($data_content);
        }
    //   dump($files);
    if($files){
        foreach($files as $fileee){
// 移动到框架应用根目录/public/uploads/ 目录下
            $info = $fileee->validate(['ext'=>'jpg,png,gif'])->move('uploads/article_img');
            if($info){
                $img_data['article_id']= $article_id;
                $img_data['pic_url'] = date('Ymd').'/'.$info->getFilename();
                Db::name('article_pic')->insert($img_data);
            }else{
// 上传失败获取错误信息
                return json(array('code'=>0,'msg'=>$fileee->geterror()));

            }
        }

    }

        if($article_id){
            return json(array('code'=>1));
        }



    }
    public function focus_or_not(){ //http://localhost/little_star/public/api/user/focus_or_not?token=9640bf730fe7f3759b8d67780d592b8f2853e308&user_focus_id=10004
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $user_focus_id = $_REQUEST['user_focus_id'];
        $focus_id=Db::name('user_focus')
            ->where(array('fan_id'=>$user_id,'follow_id'=>$user_focus_id,'delete_time'=>0))
            ->column('id');
        //dump($focus_id);
        //echo $focus_id[0];
        if($focus_id){//已经关注过，要取消关注
            Db::name('user_focus')
                ->update(array('id'=>$focus_id[0],'delete_time'=>time()));
            return json(array('code'=>1,'msg'=>'已取消关注'));
        }
        else{
            $user_focus=Db::name('user_focus')
                ->where(array('fan_id'=>$user_id,'follow_id'=>$user_focus_id))
                ->column('id');
            if($user_focus){//以前有过记录
                Db::name('user_focus')
                    ->update(array('id'=>$user_focus[0],'delete_time'=>0));
                return json(array('code'=>1,'msg'=>'已关注'));
            }
            else{
                $data['fan_id']= $user_id;
                $data['follow_id']=$user_focus_id;
                $data['create_time']= time();
                Db::name('user_focus')->insert($data);
            }
            return json(array('code'=>1,'msg'=>'已关注'));
        }
    }

    public function love_article(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $article_id = $_REQUEST['article_id'];
        $love_id=Db::name('article_love')
            ->where(array('love_user'=>$user_id,'article_id'=>$article_id,'delete_time'=>0))
            ->column('id');
        //dump($focus_id);
        //echo $focus_id[0];
        if($love_id){//已经关注过，要取消关注
            Db::name('article_love')
                ->update(array('id'=>$love_id[0],'delete_time'=>time()));
            Db::name('comment_reply')->where('id',$article_id)->setDec('praise_num');
            return json(array('code'=>1,'msg'=>'已取消喜欢'));
        }
        else{
            $user_focus=Db::name('article_love')
                ->where(array('love_user'=>$user_id,'article_id'=>$article_id))
                ->column('id');
            if($user_focus){//以前有过记录
                Db::name('article_love')
                    ->update(array('id'=>$user_focus[0],'delete_time'=>0));
                return json(array('code'=>1,'msg'=>'已喜欢'));
            }
            else{
                $data['author_id']=Db::name('article')->where('id',$article_id)->column('author');
                $data['love_user']= $user_id;
                $data['article_id']=$article_id;
                $data['create_time']= time();
                Db::name('article')->where('id',$article_id)->setInc('praise_num');
                Db::name('article_love')->insert($data);
            }
            return json(array('code'=>1,'msg'=>'已喜欢'));
        }
    }

    public function praise_comment(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $comment_id = $_REQUEST['comment_id'];
        $love_id=Db::name('praise_comment')
            ->where(array('love_user'=>$user_id,'comment_id'=>$comment_id,'delete_time'=>0))
            ->column('id');
        //dump($focus_id);
        //echo $focus_id[0];
        if($love_id){//已经关注过，要取消关注
            Db::name('praise_comment')
                ->update(array('id'=>$love_id[0],'delete_time'=>time()));
            Db::name('comment_reply')->where('id',$comment_id)->setDec('praise_num');
            return json(array('code'=>1,'msg'=>'已取消喜欢'));
        }
        else{
            $user_focus=Db::name('praise_comment')
                ->where(array('love_user'=>$user_id,'comment_id'=>$comment_id))
                ->column('id');
            if($user_focus){//以前有过记录
                Db::name('praise_comment')
                    ->update(array('id'=>$user_focus[0],'delete_time'=>0));
                return json(array('code'=>1,'msg'=>'已喜欢'));
            }
            else{
                $data['author_id']=Db::name('comment_reply')->where('id',$comment_id)->value('author');
                $data['love_user']= $user_id;
                $data['comment_id']=$comment_id;
                $data['praise_num'] = 0;
                $data['comment_num'] = 0;
                $data['create_time']= time();
                Db::name('praise_comment')->insert($data);
            }
            Db::name('comment_reply')->where('id',$comment_id)->setInc('praise_num');
            return json(array('code'=>1,'msg'=>'已喜欢'));
        }
    }

    public function post_list(){//获取话题文章
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $type = $_REQUEST['type'];
        if(isset($_REQUEST['page'])){
            $page = $_REQUEST['page'];
        }
        else{
            $page = 1;
        }
        $res=Db::name('article')->where('type',$type)->order('create_time desc')->limit(20*($page-1),20)->select();
        foreach($res as $k=>$v){
            $res[$k]['create_time'] = time_format($v['create_time']);
            $res[$k]['author_nickname']  = Db::name('user')->where('id',$res[$k]['author'])->value('nickname');
            $res[$k]['author_face_url']  = Db::name('user')->where('id',$res[$k]['author'])->value('pic_url');
            $res[$k]['first_para'] = Db::name('article_para')->where('article_id',$res[$k]['id'])->order('rank')->value('para');
            if($res[$k]['author_face_url']){
                $res[$k]['author_face_url']= 'http://120.79.229.151/little_star/public/uploads/face_img/'.$res[$k]['author_face_url'];
        }
            if($res[$k]['thumbnail_url']){
                $res[$k]['thumbnail_url']= 'http://120.79.229.151/little_star/public/'.$res[$k]['thumbnail_url'];
            }
            else{
                $res[$k]['thumbnail_url'] = 'http://120.79.229.151/little_star/public/uploads/thumbnail/f1HEnQ5216Xv9ed18tv6s3o26.png';
            }
        }
        if($res){
            if($res[0]['img_num']){
                $res[0]['thumbnail_url'] = 'http://120.79.229.151/little_star/public/uploads/article_img/'.Db::name('article_pic')->where('article_id',$res[0]['id'])->value('pic_url');
            }
            else{
                $res[0]['thumbnail_url'] = 'http://120.79.229.151/little_star/public/uploads/article_img/20180316/38562cdb358669a0c55f1d6cdd0862e0.png';
            }
        }

        //dump($res);
        return json($res);
        
    }
    
    public function show_article(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $article_id = $_REQUEST['article_id'];
        $content = Db::name('article_para')->where('article_id',$article_id)->field('para')->select();
/*        foreach ($article['content'] as $k=>$v){
            $article['content'][$k]['create_time'] = time_format($v['create_time']);
        }*/
        $image = Db::name('article_pic')->where('article_id',$article_id)->field('pic_url')->select();
        foreach ($image as $k=>$v){
            $img['img'][$k] = 'http://120.79.229.151/little_star/public/uploads/article_img/'.$v['pic_url'];
           // dump($v);
            //echo $v['pic_url'];
           // echo $v['pic_url'];
        }
        $arti = Db::name('article')->where('id',$article_id)->field('author, title, praise_num, comment_num,location, create_time')->select();
        $article['author'] = $arti[0]['author'];
/*        $article['info']['author_face_url'] = Db::name('user')->where('id',$arti[0]['author'])->value('pic_url');
        $article['info']['author_nickname'] = Db::name('user')->where('id',$arti[0]['author'])->value('nickname');*/
        $article['praise_num'] = $arti[0]['praise_num'];
        $article['comment_num'] = $arti[0]['comment_num'];
        $article['create_time'] = time_full_format($arti[0]['create_time']);
        $article['title'] = $arti[0]['title'];

        $location = Db::name('article')->where('id',$article_id)->value('location');
        $text_index = 0;
        $img_index = 0;
        for($i =0; $i<strlen($location);$i++){
            if(substr($location,$i,1)==0){
                //echo 0;
                $article['article'][$i]['content'] = $content[$text_index]['para'];
                $article['article'][$i]['type'] = 'text';
                $text_index++;
            }
            else {
                $article['article'][$i]['content'] = $img['img'][$img_index];
                $article['article'][$i]['type'] = 'image';
                $img_index++;
            }
        }


        return json($article);
    }

    public function get_article_comment(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $article_id = $_REQUEST['article_id'];
        $user_id = $tok['id'];
        $res['comment'] = Db::name('comment_reply')->where('root',$article_id)->field('id, author, reply_to_user, content, praise_num,create_time')->select();
        foreach ($res['comment'] as $k=>$v){
            $flag=Db::name('praise_comment')->where(array('love_user'=>$user_id,'comment_id'=>$v['id']))->find();
            $res['comment'][$k]['create_time'] = time_format($v['create_time']);
            if($flag){
                $res['comment'][$k]['is_praise'] = 1;
            }
            else{
                $res['comment'][$k]['is_praise'] = 0;
            }
        }
        $res['code'] = 1;
        return json($res);
    }

    public function comment(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        //echo 'hellol';
        //exit();
        $type = $_REQUEST['type'];
        $comment_body = $_REQUEST['comment_body'];
        $be_comment_id = $_REQUEST['be_comment_id'];

        if($type ==0){//对文章的评论
            $data['reply_to'] = 0;
            $data['root'] = $be_comment_id;
            $data['reply_to_user'] = Db::name('article')->where('id',$be_comment_id)->value('author');
            //dump($data['reply_to_user']);
        }
        else{//对评论的评论
            $data['reply_to'] =$be_comment_id;
            $data['reply_to_user'] = Db::name('comment_reply')->where('id',$be_comment_id)->value('author');
            $data['root'] = Db::name('comment_reply')->where('id',$be_comment_id)->value('root');
        }

       $data['content'] = $comment_body;
        $data['author'] = $user_id;
        $data['create_time'] = time();
               Db::name('article')->where('id',$data['root'])->setInc('comment_num');
               Db::name('comment_reply')->insert($data);
        return json(array('code'=>1));

    }



    public function chg_nickname(){//修改昵称
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $new_nickname = $_REQUEST['new_nickname'];
        $flag = Db::name('user')->where('id',$user_id)->setField('nickname', $new_nickname);
        if($flag){
            return json_encode(array('code'=>1,'msg'=>'sucess'));
        }
        else{
            return json_encode(array('code'=>0));
        }
    }

    public function chg_face(){
        if(!Cache::get($_REQUEST['token'])){
            return json(array('code'=>0,'msg'=>'token无效'));
        };
        $tok = Cache::get($_REQUEST['token']);
        $user_id = $tok['id'];
        $file = request()->file('image');
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif'])->move('uploads/face_img');
            if($info){
                $new_face_url = $info->getSaveName();

                Db::name('user')->where('id',$user_id)->setField('pic_url',$new_face_url);
                return json_encode(array('code'=>1,'msg'=>'success'));
            }else{
// 上传失败获取错误信息
                return json(array('code'=>0,'msg'=>$file->geterror()));

            }

        }
    }

}
