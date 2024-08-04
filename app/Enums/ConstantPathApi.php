<?php

declare(strict_types=1);

/**
 * Constant enum path Api.
 */

 //auth + user
const PATH_API_LOGIN = "login";
const PATH_API_LOGOUT = "logout";
const PATH_API_RESET_PASSWORD = "reset-password";
const PATH_GET_USER_INFO = "get-user-info";
const PATH_GET_ALL_USER = "get-all-user";
const PATH_SET_MOBILE_DEVICE_TOKEN = "set-device-token";
const PATH_GET_LIST_SELECT_SALE = "get-list-select-sale";
const PATH_CREATE_USER = "create-user";
const PATH_UPDATE_USER = "update-user";
const PATH_UPDATE_USER_AVATAR = "update-user-avatar";

//custommer - CSKH
const PATH_CREATE_CUSTOMER = "create-customer";
const PATH_UPDATE_CUSTOMER = "update-customer";
const PATH_DELETE_CUSTOMER = "delete-customer";
const PATH_GET_ALL_CUSTOMER = "get-all-customer";
const PATH_GET_CUSTOMER_INFO = "get-customer-info";

//custommer budget - ngan sach
const PATH_UPDATE_CUSTOMER_BUDGET = "update-customer-budget";
const PATH_GET_ALL_CUSTOMER_BUDGET = "get-all-customer-budget";
const PATH_GET_LIST_CUSTOMER_BUDGET_ACTIVE = "get-list-customer-budget-active";

//cycle budget
const PATH_GET_ALL_CYCLE_BUDGET = "get-all-cycle-budget";
const PATH_CREATE_CYCLE_BUDGET = "create-cycle-budget";
const PATH_UPDATE_CYCLE_BUDGET = "update-cycle-budget";

//task - quan ly du an
const PATH_CREATE_TASK = "create-task";
const PATH_UPDATE_TASK = "update-task";
const PATH_GET_LIST_USER_IN_TASK = "get-list-user-in-task";
const PATH_GET_ALL_TASK = "get-all-task";
const PATH_GET_TASK_INFO = "get-task-info";
const PATH_DELETE_TASK = "delete-task";
const PATH_DUPLICATE_TASK = "duplicate-task";

//sub task - quan ly cong viec
const PATH_CREATE_SUB_TASK = "create-sub-task";
const PATH_CREATE_SUB_TASK_ADS = "create-sub-task-ads";
const PATH_UPDATE_SUB_TASK = "update-sub-task";
const PATH_GET_SUB_TASK_INFO = "get-sub-task-info";
const PATH_GET_LIST_SUB_TASK_BY_TASK_ID = "get-list-sub-task";
const PATH_GET_LIST_FILE_BY_SUB_TASK_ID = "get-list-file-sub-task";
const PATH_DELETE_SUB_TASK = "delete-sub-task";

//sub task comment
const PATH_GET_SUB_TASK_COMMENT = "get-sub-task-comment";
const PATH_CREATE_SUB_TASK_COMMENT = "create-sub-task-comment";

//sub task file
const PATH_REMOVE_SUB_TASK_FILE = "remove-sub-task-file";

//notification
const PATH_GET_NOTI_BY_USER = "get-all-noti";
const PATH_UPDATE_IS_SEEN = "update-noti-is-seen";
const PATH_UPDATE_ALL_NOTI_SEEN = "update-all-noti-seen";

//history
const PATH_GET_LIST_HISTORY = "get-list-history";