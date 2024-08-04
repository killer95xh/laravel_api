<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $pathApi = $request->path();
        if (!auth()->check()) {
            $params = $request->all();
            $params['password'] = "********";
            $ip = request()->header('X-Real-IP');
            $userAgent = request()->header('User-Agent');
            Log::info("IP: " . $ip . " - " . $userAgent . " - REQUEST: " . toJson($params));
        }
        $rules = $this->getRuleApi($pathApi);
        $validation = Validator::make($request->all(), $rules['rules'], $rules['messages']);
        if ($validation->fails()) {
            $errors = [];
            foreach ($validation->errors()->toArray() as $key => $value) {
                $errors[$key] = $value[0];
            }
            $result = responseApi("422", "Validation request failed!", $errors);

            return response()->json($result, 422);
        }

        $response = $next($request);
        return $response;
    }

    function getRuleApi ($pathApi) {
        $rules = [
            "rules" => [],
            "messages" => []
        ];
        switch ($pathApi) {
            case "api/auth/" . PATH_API_LOGIN:
                $rules = [
                    'rules' => [
                        'user_name' => 'required|min:6|max:20',
                        'password' => 'required|min:6',
                    ],
                    'messages' => [
                        'user_name.required' => 'Tên đăng nhập không được để trống',
                        'user_name.min' => 'Tên đăng nhập tối thiểu 6 ký tự',
                        'user_name.max' => 'Tên đăng nhập tối đa 20 ký tự',
                        'password.min' => 'Mật khẩu tối thiểu 6 ký tự',
                        'password.required' => 'Mật khẩu không được để trống',
                    ]
                ];
                break;
            
            case "api/auth/" . PATH_API_RESET_PASSWORD:
                $rules = [
                    'rules' => [
                        'user_name' => 'required|min:6|max:20|exists:users,user_name',
                        // 'email' => [
                        //     'required',
                        //     'email',
                        //     Rule::exists('users')->where(function ($query) {
                        //         return $query->where('user_name', request('user_name'));
                        //     })
                        // ],
                    ],
                    'messages' => [
                        'user_name.required' => 'Tên đăng nhập không được để trống!',
                        'user_name.exists' => 'Tên đăng nhập không tồn tại!',
                        'user_name.min' => 'Tên đăng nhập tối thiểu 6 ký tự',
                        'user_name.max' => 'Tên đăng nhập tối đa 20 ký tự',
                        // 'email.required' => 'Email không được để trống!',
                        // 'email.email' => 'Email sai định dạng!',
                        // 'email.exists' => 'Sai thông tin email đăng ký!',
                    ]
                ];
                break;
            
            case "api/" . PATH_CREATE_USER:
            case "api/" . PATH_UPDATE_USER:
                $rules = [
                    'rules' => [
                        "first_name" => "required",
                        "last_name" => "required|alpha_num_with_spaces",
                        "user_name" => "required|unique:users,user_name|min:6",
                        "password" => "required|min:6",
                        "email" => "required|email",
                        "position" => "alpha_num_with_spaces",
                    ],
                    'messages' => [
                        'first_name.required' => 'Họ không được để trống',
                        'last_name.required' => 'Tên không được để trống',
                        'user_name.required' => 'Tên đăng nhập không được để trống',
                        'password.required' => 'Mật khẩu không được để trống',
                        'email.required' => 'Email không được để trống',

                        'email.email' => 'Email sai định dạng',

                        'user_name.unique' => 'Tên đăng nhập đã tồn tại',

                        // 'first_name.min' => 'Họ tối thiểu 6 ký tự',
                        'user_name.min' => 'Tên đăng nhập tối thiểu 6 ký tự',
                        'password.min' => 'Mật khẩu tối thiểu 6 ký tự',

                        'last_name.alpha_num_with_spaces' => 'Tên không được chứa ký tự đặc biệt!',
                        'position.alpha_num_with_spaces' => 'Chức vụ không được chứa ký tự đặc biệt!',
                    ]
                ];
                if ($pathApi == "api/" . PATH_UPDATE_USER) {
                    $rules['rules']['user_name'] = [
                        "required", "min:6",
                        Rule::unique('users', 'user_name')->ignore(request('id'), 'id')
                    ];
                }
                break;
            case "api/" . PATH_UPDATE_USER_AVATAR:
                $rules = [
                    'rules' => [
                        "avatar" => "required",
                    ],
                    'messages' => [
                        'avatar.required' => 'Thêm ảnh avatar!',
                    ]
                ];
                break;
                
            case "api/" . PATH_CREATE_CUSTOMER:
            case "api/" . PATH_UPDATE_CUSTOMER:
                if (array_key_exists('api_from', request()->all()) && request()->api_from == 'mobile') {
                    //
                } else {
                    $rules = [
                        'rules' => [
                            "sale_user_id" => "required",
                            "customer_name" => "required",
                            "phone_number" => "required",
                            "customer_source" => "required",
                            "care_progress" => "required",
                            "deal_status" => "required",
                            "callback_due_date" => "required"
                        ],
                        'messages' => [
                            'sale_user_id.required' => 'Sale chăm sóc không được để trống',
                            'customer_name.required' => 'Tên khách hàng không được để trống',
                            'phone_number.required' => 'Số điện thoại không được để trống',
                            'customer_source.required' => 'Nguồn không được để trống',
                            'care_progress.required' => 'Tiến độ chăm sóc chăm sóc không được để trống',
                            'deal_status.required' => 'Trạng thái không được để trống',
                            'callback_due_date.required' => 'Ngày gọi lại không được để trống'
                        ]
                    ];
                }
                break;

            case "api/" . PATH_CREATE_TASK:
                $rules = [
                    'rules' => [
                        "customer_id" => "required",
                        "project_name" => "required",
                        "task_supervisor_user_id" => "required",
                        "priority_level" => "required",
                        "contract_start_date" => "required",
                        "contract_end_date" => "required"
                    ],
                    'messages' => [
                        'customer_id.required' => 'Thông tin khách hàng không được để trống',
                        'project_name.required' => 'Tên dự án không được để trống',
                        'task_supervisor_user_id.required' => 'Tổng phụ trách không được để trống',
                        'priority_level.required' => 'Độ ưu tiên không được để trống',
                        'contract_start_date.required' => 'Thời gian hợp đồng không được để trống',
                        'contract_end_date.required' => 'Thời gian hợp đồng không được để trống'
                    ]
                ];
                if (request()->check_create_sub_task == true) {
                    $rules['rules']['sub_task_name'] = "required";
                    $rules['rules']['leader_user_id'] = "required";
                    // $rules['rules']['employee_user_id'] = "required";
                    $rules['messages']['sub_task_name.required'] = 'Tên công viêc không được để trống';
                    $rules['messages']['leader_user_id.required'] = 'Tên trường phòng không được để trống';
                    // $rules['messages']['employee_user_id.required'] = 'Nhân viên không được để trống';
                }
                break;

            case "api/" . PATH_CREATE_SUB_TASK:
                $rules = [
                    'rules' => [
                        "sub_task_name" => "required",
                        "leader_user_id" => "required",
                        // "employee_user_id" => "required",
                    ],
                    'messages' => [
                        'sub_task_name.required' => 'Tên công việc không được để trống',
                        'leader_user_id.required' => 'Trưởng phòng được để trống',
                        // 'employee_user_id.required' => 'Người thực hiện trách không được để trống'
                    ]
                ];
                break;

            case "api/" . PATH_CREATE_CYCLE_BUDGET:
            case "api/" . PATH_UPDATE_CYCLE_BUDGET:
                $rules = [
                    'rules' => [
                        "cycle_name" => "required",
                    ],
                    'messages' => [
                        'cycle_name.required' => 'Tên giai đoạn không được để trống',
                    ]
                ];
                break;

            default:
                break;
        }

        return $rules;
    }
}
