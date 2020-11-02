<?php

declare (strict_types = 1);

namespace Larke\Admin\Http;

/*
 * 响应代码
 *
 * 默认0 没有错误
 * 默认1 默认错误
 * 
 * 1开头 登陆退出等错误
 * 2开头 格式等错误
 * 3开头 转移等相关
 * 4开头 数据没有找到
 * 5开头 存储修改等错误
 * 
 * 可使用：
 * ResponseCode::SUCCESS = LARKE_ADMIN_SUCCESS
 *
 * @create 2020-11-1
 * @author deatil
 */
class ResponseCode
{
    const SUCCESS = 0; // LARKE_ADMIN_SUCCESS
    const ERROR = 1; // LARKE_ADMIN_ERROR
    const INVALID = 99999; // LARKE_ADMIN_INVALID
    const UNKNOWN = 99998; // LARKE_ADMIN_UNKNOWN
    const EXCEPTION = 99997; // LARKE_ADMIN_EXCEPTION
    
    const LOGIN_ERROR = 101; // LARKE_ADMIN_LOGIN_ERROR
    const LOGOUT_ERROR = 102; // LARKE_ADMIN_LOGOUT_ERROR
    const ACCESS_TOKEN_ERROR = 103; // LARKE_ADMIN_ACCESS_TOKEN_ERROR
    const REFRESH_TOKEN_ERROR = 104; // LARKE_ADMIN_REFRESH_TOKEN_ERROR
    const AUTH_ERROR = 105; // LARKE_ADMIN_AUTH_ERROR
    const ACCESS_TOKEN_TIMEOUT = 106; // LARKE_ADMIN_ACCESS_TOKEN_TIMEOUT
    const REFRESH_TOKEN_TIMEOUT = 107; // LARKE_ADMIN_REFRESH_TOKEN_TIMEOUT
    
    const EMPTY_PARAM = 200; // LARKE_ADMIN_EMPTY_PARAM
    const PARAM_INVALID = 201; // 参数无效 LARKE_ADMIN_PARAM_INVALID
    const JSON_PARSE_FAIL = 202; // LARKE_ADMIN_JSON_PARSE_FAIL
    const TYPE_ERROR = 203; // LARKE_ADMIN_TYPE_ERROR
    
    const NOT_EXISTS = 400; // LARKE_ADMIN_NOT_EXISTS
    const DATA_EXISTS = 401; // LARKE_ADMIN_DATA_EXISTS
    const ACCESS_TOKEN_EMPTY = 402; // LARKE_ADMIN_ACCESS_TOKEN_EMPTY
    const REFRESH_TOKEN_EMPTY = 403; // LARKE_ADMIN_REFRESH_TOKEN_EMPTY

    const FILE_SAVE_FAILED = 500; // LARKE_ADMIN_FILE_SAVE_FAILED
    const RECORD_NOT_FOUND = 501; // 记录未找到 LARKE_ADMIN_RECORD_NOT_FOUND
    const DELETE_FAILED = 502; // 删除失败 LARKE_ADMIN_DELETE_FAILED
    const CREATE_FAILED = 503; // 添加记录失败 LARKE_ADMIN_CREATE_FAILED
    const UPDATE_FAILED = 504; // 添加记录失败LARKE_ADMIN_ UPDATE_FAILED

    public static function getConstants(): array 
    {
        $rClass = new \ReflectionClass(__CLASS__);

        return $rClass->getConstants();
    }
}
