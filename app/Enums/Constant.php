<?php

declare(strict_types=1);

/**
 * Constant enum.
 */
const KEY_HIDDEN_LOG = ['password', '_token', 'old_password', 'new_password', 'confirm_new_password'];

//email config
const EMAIL_CONFIG_HOST = 'smtp.gmail.com';
const EMAIL_CONFIG_PORT = 587;
const EMAIL_CONFIG_USERNAME = 'oceanlink003@gmail.com';
const EMAIL_CONFIG_PASSWORD = 'gbyzgqgpaxiixexo';
const EMAIL_CONFIG_FROM_ADDRESS = 'oceanlink003@gmail.com';
const EMAIL_CONFIG_NAME = 'OCEANLINK1975';

const TYPE_NOTI = [
    'CusTomCare' => "1",  //CSKH
    'FinancialManagement' => "2", //Ngan sach 
    'FinancialManagementSub' => "3", //Ngan sach 
    'TaskDetail' => "4", //Chi tiet du an sap den deadline
    'TaskDetailll' => "5", //Chi tiet du an duoc giao
    'SubTaskComment' => "6", //comment cong viec
    'SubTask' => "7",
    'UpdateAssignedSubTask' => "8", // up assigned cong viec
    'UpdateStatusSubTask' => "9", // up status cong viec
    'NotiCSKH20h' => "10",
    'OutDateCSKH' => "11",
    'TagComment' => "12",
    'DeadlineSubTask1' => "13", //sub task sap den han
    'DeadlineSubTask2' => "14", //sub task da qua han
];
const TYPE_HISTORY = [
    "TYPE1" => "1",
    "TYPE2" => "2",
    "TYPE3" => "3",
    "TYPE4" => "4",
];

const SUB_TASK_STATUS =  [
    "1" => "Chưa triển khai",
    "2" => "Làm lại",
    "3" => "Đang thực hiện",
    "4" => "Chờ xét duyệt",
    "5" => "Đã xét duyệt",
    "6" => "Đã bàn giao",
];

const SUB_TASK_PRIORITY_LEVEL =  [
    "1" => "Thấp",
    "2" => "Bình thường",
    "3" => "Cao",
    "4" => "Rât cao",
];
