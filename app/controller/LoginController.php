<?php
/**
 * 登录
 */
namespace app\controller;

use App\Helpers\AccessKeyHelper;
use App\Models\District;
use App\Models\User;
use Cache;
use think\Session;
use app\util\ValidateHelper;


class LoginController extends BaseController
{
    protected $checkLogin = false;

    /**
     * 手机号登录&注册
     */
    public function loginByPhone()
    {

        if (!$this->request->isPost()) {
            return $this->outJson(500,"非法请求");

            $mobile = $this->request->post("mobile",'',"intval");
            $vcode = $this->request->post("vcode",'',"intval");

            if(ValidateHelper::isMobile($mobile) == false || $vcode <= 0){
                return $this->outJson(100,"参数错误");
            }

            $this->user_data = User::where('mobile_phone',$this->request_data['phone'])->first();

            if (empty($this->user_data->password)) {
                $result['errcode'] = 500;
                $result['errmsg'] = '账号不存在，请重新输入';
                Log::error(__FILE__ . ":" . __LINE__ . ' ' . '用户端接口：登录失败，账号不存在，请重新输入。 ' . array_to_json($result) . array_to_json($this->user_data), $this->request_data);
                return array_to_json($result);
            }
            if ($this->user_data->lock_state == 1 && strtotime($this->user_data->lock_time) > time()) {
                $result['errcode'] = 500;
                $result['errmsg'] = '密码输入多次错误 请1小时后再试';
                Log::error(__FILE__ . ":" . __LINE__ . ' ' . '用户端接口：登录失败，密码输入多次错误 请1小时后再试。 ' . array_to_json($result), $this->request_data);
                return array_to_json($result);
            }
            $pwd_err_cnt = 5;
            if ($this->request_data['password'] != $this->user_data->password) {
                $this->user_data->password_errorcount = (int)$this->user_data->password_errorcount + 1;
                if ($this->user_data->password_errorcount >= $pwd_err_cnt) {
                    //密码输入错误5次锁定一小时
                    $this->user_data->lock_state = 1;
                    $this->user_data->lock_time = my_date_format(time() + 3600);
                }
                $this->user_data->save();
                $result['errcode'] = 500;
                $result['errmsg'] = $this->user_data->password_errorcount > 3 ? "密码输入错误超过5次账号将被锁定" : '密码不正确';
                Log::error(__FILE__ . ":" . __LINE__ . ' ' . '用户端接口：登录失败，密码不正确。 ' . array_to_json($result), $this->request_data);
                return array_to_json($result);
            }
            DB::beginTransaction();
            $result['errcode'] = 0;
            $result['errmsg'] = '登录成功';

            $data = array(
                'user_id' => $this->user_data->user_id,
                'user_name' => $this->user_data->user_name,
                'pic_url' => get_qiniu_url($this->user_data->pic_url,100,100),
                'mobile_phone' => $this->user_data->mobile_phone,
                'nickname' => $this->user_data->nickname,
                'sex' => $this->user_data->sex,
                'birthday' => $this->user_data->birthday,
                'qq_id' => $this->user_data->qq_id,
                'login_time' => $this->user_data->login_time,
                'audit_status' => $this->user_data->audit_status,
                'professional_cert_status' => $this->user_data->professional_cert_status,
            );
            $this->user_data->login_time = date('Y-m-d H:i:s'); //更新登录时间
            if (!empty($this->request_data['client_id'])) {
                $this->user_data->client_id = $this->request_data['client_id'];
            }
            $this->user_data->os = strtolower(@$this->request_data['system_name']);
            $this->user_data->os_version = @$this->request_data['system_version'];
            $this->user_data->app_version = @$this->request_data['app_version'];
            $this->user_data->device_code = @$this->request_data['uuid'];
            $this->user_data->password_errorcount = 0;
            $this->user_data->lock_state = 0;
            if (empty($this->user_data->first_login)) {
                $this->user_data->first_login = my_date_format(time());
            }
            $this->user_data->save();

            $address = $this->user_data->addresses()
                ->where('address_type', 1)
                ->where('address_status', 0)
                ->first(); // 增加返回用户默认地址
            $default_address = array();
            if ($address && !empty($address->address_id)) {
                $District = new District();
                $region = $District->recursion($address->district);
                $default_address['address_id'] = $address->address_id;
                $default_address['province_name'] = $region[1];
                $default_address['city_name'] = $region[2];
                $default_address['district_name'] = $region[3];
                $default_address['detail_address'] = $address->detail_address;
                $default_address['address_linkman'] = $address->address_linkman;
                $default_address['contact_phone'] = $address->contact_phone;
            }
            $data['default_address'] = @$default_address;
            DB::commit();
            $from = isset($request->init_data['from']) ? $request->init_data['from'] : "pc"; //客户端来源
            $data["access_key"] = AccessKeyHelper::generateAccessKey($data["user_id"],$from); //生成access_key
            Cache::forget('user_id_' . $this->user_data->user_id);
            Cache::forget('phone_' . $this->user_data->phone);
            $result['errcode'] = 0;
            $result['data'] = $data;
        }else{
            return $this->outJson(500,"非法请求");
        }
    }

    /**
     * 退出登录
     */
    public function loginOut()
    {
        if ($this->request->isAjax()) {
            if($this->getCurrentUserId() != ''){
                @Session::clear();
                @Session::destroy();
            }
            return $this->outJson(0,'退出成功',[
                'url' => $this->entranceUrl . "/login/index"
            ]);
        }
    }
}
