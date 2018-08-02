<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;
use App\LqUser;
use App\LqUserActi;
use App\LqActivity;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\LqTag;
use App\LqUserTag;

class LqControl extends Controller
{

    /**
     *
     * @return Response
     * @author bangquan zhang
     */
    public function login(Request $request)
    {

        $code = $request->get('request_code');
        $appid = 'wx9858e52a39c4c62b';
        $secret ='d6fb671601467954b68d215c2e0964dd';
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='. $code
            .'&grant_type=authorization_code';

        // 获取url返回的数据，并且解析
        $wechatRes =file_get_contents($url);
        // json 解析
        $resp = json_decode($wechatRes, true);

        if( !isset( $resp['openid']) )
            return response()->json([
                "code" => 400,
                "msg" => "not found openid",
                "wechat_id" => ""
            ]);


        $imageUrl = $request->get('image_url');  // 这是一个数组
        $name = $request->get('name');
        $gender = $request->get('gender');  // 这是一个数组
        $place = $request->get('place');  // 这是一个数组
        $result = LqUser::where('wechat_id',  $resp['openid'] )->get();
        $loginTime = Carbon::now();

        if( $result->isEmpty())
            // 插入新用户
            LqUser::insert(['wechat_id' => $resp['openid'], 'image_url' => $imageUrl, 'name' => $name,
                'gender' => $gender, 'place' =>$place, 'register_time' => $loginTime,'last_login_time' => $loginTime]);
            ;

        return response()->json([
            "code" => 200,
            "msg" => "success",
            "wechat_id" => $resp['openid']
        ]);
    }


    /**
     *
     * @return Response
     * @author bangquan zhang
     */
    public function getOwnDetails( $wechat_id)
    {
        $result = LqUser::where('wechat_id', $wechat_id)->get();

        if( $result->isEmpty())
            return response()->json([
                "code" => 400,
                "msg" => "failed",
                "user_info" => ""
            ]);

        return response()->json([
            "code" => 200,
            "msg" => "success",
            "user_info" => $result
        ]);

    }
    /*增加个人的标签
     *
     */
        public function personalTag( Request $request)
    {

        $result=LqUser::where('wechat_id',$request->input('wechat_id'))->get();
        if ($result->isEmpty())
            return response()->json([
                "code" => 401,
                "msg"  => "failed",

            ]);
        $resultTag=LqTag::where('id',$request->input('tag'))->get();
        if ($resultTag->isEmpty())
            return response()->json([
                "code" =>400,
                "msg"  =>"failed",
            ]);
        //保证数据库已有用户及正确的标签
        $tag=new LqUserTag();
        $tag->id_user=$request->input('wechat_id');
        $tag->id_tag=$request->input('tag');
        $bool=$tag->save();
	return response()->json([
		"code" =>200,
		"msg"  => "success"
	]);

	}

    /*获得标签信息
     */

    public function infoTag(Request $request)
    {
	    $result=$request->input('wechat_id');
	    $result=str_replace('"','',$result);
	    $resultFind=LqUser::where('wechat_id',$result)->get();
	     
	    if ($resultFind->isEmpty())
	    
		    return response()->json([
	          	    "code" =>400,
			    "msg"  =>"fail"
		    ]);
	    $tag=LqUserTag::where('id_user',$result)->get();
	    $arr=array();
	    
	    foreach ($tag as $i)
	    {
		    $arr[]=$i->id_tag;
		    
	    }
	    return response()->json([
	    	"code" =>200,
		"msg"  =>"success",
		"tag"  =>$arr
	    
	    ]);
	   
	  }
     
      /*用户推荐
      */
      public function recommend(Request $request)
      {
            $result=$request->input('wechat_id');
            $result=str_replace('"','',$result);
            $resultFind=LqUser::where('wechat_id',$result)->get();

            if ($resultFind->isEmpty())

               return response()->json([
                        "code" =>400,
                        "msg"  =>"fail"
		]);

	    $timedate=date("Y-m-d");
	   $time=date("h:m:s"); 
	    $resulttime=LqActivity::whereDate('date','>=',$timedate)->get();
	    if (count($resulttime)<10)
	    {
		    $arr=array();
		    foreach ($resulttime as $i)
			    $arr[]=$i->activity_id;
		    return response()->json([
			    "code" =>200,
			    "msg"  =>"success",
			    "activity" =>$arr
		    
		    ]);
		    
	    }

      } 



    /**
     * put 无法获得数据，故改为post
     * @return Response
     * @author bangquan zhang
     */
    public function updateOWnDetails(Request $request, $wechat_id)
    {

        $result = LqUser::where('wechat_id', $wechat_id)->get();

        if( $result->isEmpty())
            return response()->json([
                "code" => 400,
                "msg" => "Not found user",
            ]);


        $name = $request->input('name',$result[0]['cname']);
        $gender = $request->input('gender',$result[0]['gender']);
        $age = $request->input('age',$result[0]['age']);
        $career = $request->input('career',$result[0]['career']);
        $specialty = $request->input('specialty',$result[0]['specialty']);
        $place = $request->input('place',$result[0]['place']);

        LqUser::where('wechat_id', $wechat_id) ->update([
            'name' => $name ,
            'gender' => $gender,
            'age' => $age,
            'career' => $career,
            'specialty' => $specialty,
            'place' => $place
        ]);

        return response()->json([
            "code" => 200,
            "msg" => "success"
        ]);
    }

}
