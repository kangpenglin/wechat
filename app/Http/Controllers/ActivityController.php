<?php
/**
 * Created by PhpStorm.
 * User: jackey
 * Date: 2018/7/16
 * Time: 8:43 PM
 */

namespace App\Http\Controllers;

use Faker\Provider\DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use function Sodium\add;
use Carbon\Carbon;


/**
 * Class ActivityController
 * @package App\Http\Controllers
 */
class ActivityController extends Controller
{


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * create activity
     */
    public function createActivity(Request $request)
    {
        $wechat_id = $request->input("wechat_id");
        $aname = $request->input("aname");
        $type = $request->input("atype");
        $place = $request->input("place");
        $date = $request->input("date");
        $time = $request->input("time");
//        $capture = $request->input("capture");
        $description = $request->input("description");
        $costType = $request->input("cost_type");
        $costValue = $request->input("cost_value");
        $activity_status = $request->input("activity_status");
        $code = 400;
        $msg = "success";
        $activity_id = DB::table('activity')->insertGetId(["aname" => $aname, "place" => $place, "date" => $date, "time" => $time, "description" => $description, "cost_type" => $costType, "cost_value" => $costValue, "activity_status" => $activity_status, "atype" => $type]);
        $id = DB::table('user_acti')->insertGetId(["wechat_id" => $wechat_id, "activity_id" => $activity_id, "is_sponsor" => 1]);
        if ($activity_id > 0 && $id > 0) {//actvity and user_acti both updated
            $code = 200;
            return response()->json(["code" => $code, "msg" => $msg, "activity_id" => $activity_id]);

        } else {
            $msg = "create activity failed,please try again!!";
            return response()->json(["code" => $code, "msg" => $msg]);
        }


    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * get all activities
     */
    public function getAllActivity()
    {
        $code = 400;
        $msg = "success";
        $wechat_id = Route::input("wechat_id");
        $no_participate_list = DB::select("select * from activity where activity_id not  in (select activity_id from user_acti where wechat_id=?)", [$wechat_id]);
        $participate_list = DB::select("select * from activity where activity_id  in (select activity_id from user_acti where wechat_id=?)", [$wechat_id]);       
		
		$participate_list = json_decode(json_encode($participate_list), true);
        $no_participate_list = json_decode(json_encode($no_participate_list), true);

		$nowdate = Carbon::now()->toDateString();
        $nowtime = Carbon::now()->toTimeString();
        for($i = 0; $i < count($participate_list); $i++ )
		{
			if($participate_list[$i]["activity_status"] == 3 )
				continue;
			$participate_list[$i]["activity_status"] = 2;
			if( $participate_list[$i]["date"] >= $nowdate)
			{
				if( $participate_list[$i]["time"] >= $nowtime)
					$participate_list[$i]["activity_status"] = 0;
			}
		}


		for($i = 0; $i < count($no_participate_list); $i++ )
		{
			if($no_participate_list[$i]["activity_status"] == 3 )
				continue;
			$no_participate_list[$i]["activity_status"] = 2;
			if( $no_participate_list[$i]["date"] >= $nowdate)
				{
					if( $no_participate_list[$i]["time"] >= $nowtime)
						$no_participate_list[$i]["activity_status"] = 0;
				}
		}
		
		if (count($participate_list) != 0 | count($no_participate_list) != 0) {
            $code = 200;
            return response()->json(["code" => $code, "msg" => $msg, "participate_list" => $participate_list, "no_participate_list" => $no_participate_list]);

        } else {
            $msg = "there has no data in the database";
            return response()->json(["code" => $code, "msg" => $msg]);


        }


    }

    /**
     * @param Request $requestß
     * delete activity that whomself created
     * @return \Illuminate\Http\JsonResponse
     * 200:success
     * 401:unauthorized
     */
    public function deleteActivity(Request $request)
    {
        $wechatId = Route::input("wechat_id");
        $activityId = Route::input("activity_id");
//        echo "$activityId:$wechatId";
        $code = 400;
        $msg = "success";

        $query_result = DB::select("select is_sponsor from user_acti where wechat_id=? and activity_id=?", [$wechatId, $activityId]);

        if (count($query_result) == 1) {//the man has authorization to delete the activity
            $code = 200;
            $affected = DB::update("update activity set activity_status = ?  where activity_id = ?", [3, $activityId]);
            if ($affected == 1) {
                $msg = "the status has updated";
            } else
                $msg = "there is nothing todo with the status";
            return response()->json(["code" => $code, "msg" => $msg, "affected" => $affected]);
        } else {
            $code = 401;
            $msg = "sorry,you cannot delete this activity";
            return response()->json(["code" => $code, "msg" => $msg]);

        }


    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * update activity information
     */

    public function updateActivity(Request $request)
    {
        $wechat_id = Route::input("wechat_id");
        $activity_id = Route::input("activity_id");
        $aname = $request->input("aname");
        $atype = (integer)$request->input("atype");
        $place = $request->input("place");
        $date = $request->input("date");
        $time = $request->input("time");
//        $capture = $request->input("capture");
        $description = $request->input("description");
        $costType =(integer) $request->input("cost_type");
        $costValue = $request->input("cost_value");
        $activity_status = $request->input("activity_status");
        $code = 400;
        $msg = "success";
//        echo $aname.$atype.$description;
        $query_result = DB::select("select * from user_acti where wechat_id=? and activity_id=? and is_sponsor=1", [$wechat_id, $activity_id]);
//        print_r($query_result);
        if (count($query_result) == 1) {//the man has authorization to delete the activity
            $code = 200;
            $affected = DB::update("update activity set aname=?,atype=?,place=?,date=?,time=?,description=?,cost_type=?,cost_value=?,activity_status=? where activity_id=?",
                [$aname, $atype, $place, $date, $time, $description, $costType, $costValue, $activity_status, $activity_id]);
            if ($affected == 1) {
                $msg = "the status has updated";
            } else
                $msg = "there is nothing todo with the status";
            return response()->json(["code" => $code, "msg" => $msg, "affected" => $affected]);

        } else {
            $msg = "Error:You can not update the data!";
            return response()->json(["code" => $code, "msg" => $msg]);
        }

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * search activity by aname
     */

    public function searchActivity()
    {
        $search_key = Route::input("search_key");
        $search_key = str_replace(' ', '', $search_key);
		$code = 400;
        $msg = "success";

		if( strlen($search_key) == 0 )
		{
            $msg = "key is null";
			return response()->json(["code" => $code, "msg" => $msg]);
		}
//        echo $search_key;
        /**
         * 模糊匹配查找
         */

        $activity_list = DB::select("select * from activity where  aname like " . "'%" . $search_key . "%'");
//        print_r($activity_list);


        $activity_list = json_decode(json_encode($activity_list), true);
        $nowdate = Carbon::now()->toDateString();
        $nowtime = Carbon::now()->toTimeString();
        for($i = 0; $i < count($activity_list); $i++ )
		{	
			if($activity_list[$i]["activity_status"] == 3 )
                continue;
            $activity_list[$i]["activity_status"] = 2;
            if( $activity_list[$i]["date"] >= $nowdate)
			{
                if( $activity_list[$i]["time"] >= $nowtime)
                    $activity_list[$i]["activity_status"] = 0;
			}
		}



        if (count($activity_list) == 0) {
            $code = 402;
            $msg = "can not find activity as the key";
            return response()->json(["code" => $code, "msg" => $msg]);

        } else {
            $code = 200;
            return response()->json(["code" => $code, "msg" => $msg, "activity_list" => $activity_list]);
        }

    }


    /**
     * @return \Illuminate\Http\JsonResponse
     * show activity assosiated with wechat_id
     */
    public function getOwnActivity()
    {
        $wechat_id = Route::input("wechat_id");
        $code = 400;
        $msg = "";
        $created_list = DB::select("select * from activity where activity_id in (select activity_id from user_acti where wechat_id=? and is_sponsor=1)", [$wechat_id]);
        $participate_list = DB::select("select * from activity where activity_id in (select activity_id from user_acti where wechat_id=? and is_sponsor=0)", [$wechat_id]);


		
		$nowdate = Carbon::now()->toDateString();
		$nowtime = Carbon::now()->toTimeString();
		
        $participate_list = json_decode(json_encode($participate_list), true);
		for($i = 0; $i < count($participate_list); $i++ )
		{
			if($participate_list[$i]["activity_status"] == 3 )
				continue;
			$participate_list[$i]["activity_status"] = 2;
			if( $participate_list[$i]["date"] >= $nowdate)
			{
				if( $participate_list[$i]["time"] >= $nowtime)
					$participate_list[$i]["activity_status"] = 0;
			}
		}
		
		
        $created_list = json_decode(json_encode($created_list), true);
        for($i = 0; $i < count($created_list); $i++ )
		{
            if($created_list[$i]["activity_status"] == 3 )
				continue;
            $created_list[$i]["activity_status"] = 2;
            if( $created_list[$i]["date"] >= $nowdate)
			{
                if( $created_list[$i]["time"] >= $nowtime)
                    $created_list[$i]["activity_status"] = 0;
			}
		}
		
		
		if (count($created_list) != 0 | count($participate_list) != 0) {
            $code = 200;
            $msg = "success";
            return response()->json(["code" => $code, "msg" => $msg, "created_list" => $created_list, "participate_list" => $participate_list]);

        } else {
            $code = 402;
            $msg = "can not find any activities!";
            return response()->json(["code" => $code, "msg" => $msg]);
        }

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * show details
     */
    public function getActivityDetails()
    {
        $wechat_id = Route::input("wechat_id");
        $activity_id = Route::input("activity_id");
        $code = 400;
        $msg = "";

        $activity = DB::select("select * from activity where activity_id=?", [$activity_id]);
        $isIn = DB::select("select * from user_acti where wechat_id=? and activity_id=?", [$wechat_id, $activity_id]);
        $is_participate = count($isIn) == 0 ? false : true;
        $isS = DB::select("select * from user_acti where wechat_id=? and activity_id=? and is_sponsor=1", [$wechat_id, $activity_id]);
        $is_sponsor = count($isS) == 0 ? false : true;

        $user_list = DB::select("select * from user where wechat_id in (select wechat_id from user_acti where activity_id=?) ", [$activity_id]);
        $count = count($user_list);
        if (count($activity) == 0) {
            $code = 400;
            $msg = "the activity is not exist";
            return response()->json(["code" => $code, "msg" => $msg]);

        } else {
            $code = 200;
            $msg = "success";
            return response()->json(["code" => $code, "msg" => $msg, "activity" => $activity, "user_list" => $user_list, "count" => $count, "is_participate" => $is_participate, "is_sponsor" => $is_sponsor]);

        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * shift activity to another
     */

    public function shiftActivity(Request $request)
    {
        $wechat_id = $request->input("wechat_id");
//        echo $wechat_id;
        $code = 400;
        $msg = "";
        $activity_id = $request->input("activity_id");
        $wechat_id_to = $request->input("wechat_id_to");
        $isSponsor = DB::select("select * from user_acti where activity_id=? and wechat_id=? and is_sponsor=1", [$activity_id, $wechat_id]);
        if (count($isSponsor) == 0) {
            $code = 401;
            $msg = "unauthorized";
            return response()->json(["code" => $code, "msg" => $msg]);

        } else {
            $affected1 = DB::update("update user_acti set is_sponsor=0 where activity_id=? and wechat_id=?", [$activity_id, $wechat_id]);
            $affected2 = DB::update("update user_acti set is_sponsor=1 where activity_id=? and wechat_id=?", [$activity_id, $wechat_id_to]);

            if ($affected1 != 0 && $affected2 != 0) {
                $code = 200;
                $msg = "success";
                return response()->json(["code" => $code, "msg" => $msg]);

            } else {
                $code = 400;
                $msg = "failed";
                return response()->json(["code" => $code, "msg" => $msg]);
            }
        }

    }


    public function participateActivity(Request $request)
    {
        $wechat_id = $request->input("wechat_id");
        $activity_id = $request->input("activity_id");
        $code = 400;
        $msg = "success";
        $re=DB::select("select * from user_acti where activity_id=? and wechat_id=?",[$activity_id,$wechat_id]);
        if(count($re)!=0){
            $code=600;
            $msg="duplicated";
            return response()->json(["code" => $code, "msg" => $msg]);
        }


        else{
            $id = DB::table('user_acti')->insertGetId(["wechat_id" => $wechat_id, "activity_id" => $activity_id, "is_sponsor" => 0]);
            if ($id > 0) {
                $code = 200;
                return response()->json(["code" => $code, "msg" => $msg]);

            } else {
                $msg = "error";
                return response()->json(["code" => $code, "msg" => $msg]);
            }
        }

    }

    public function unparticipateActivity()
    {
        $wechat_id = Route::input("wechat_id");
        $activity_id = Route::input("activity_id");
        $code = 400;
        $msg = "success";
      $affected = DB::delete("delete  from user_acti where wechat_id=? and activity_id=?", [$wechat_id,$activity_id]);
      if ($affected == 1) {
          $code = 200;
         return  response()->json(["code" => $code, "msg" => $msg]);
      } else
          $msg = "error";
       return  response()->json(["code" => $code, "msg" => $msg]);

                 }


}
