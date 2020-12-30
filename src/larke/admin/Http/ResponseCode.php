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
 * use:
 * \ResponseCode::SUCCESS
 *
 * @create 2020-11-1
 * @author deatil
 */
class ResponseCode
{
    const SUCCESS = 0; 
    const ERROR = 1; 
    const EXCEPTION = 99997;
    const UNKNOWN = 99998; 
    const INVALID = 99999;
    
    const LOGIN_ERROR = 101;
    const LOGOUT_ERROR = 102;
    const ACCESS_TOKEN_ERROR = 103;
    const REFRESH_TOKEN_ERROR = 104;
    const AUTH_ERROR = 105;
    const ACCESS_TOKEN_TIMEOUT = 106;
    const REFRESH_TOKEN_TIMEOUT = 107;
    
    const EMPTY_PARAM = 200;
    const PARAM_INVALID = 201; // 参数无效
    const JSON_PARSE_FAIL = 202;
    const TYPE_ERROR = 203;
    
    const NOT_EXISTS = 400;
    const DATA_EXISTS = 401;
    const ACCESS_TOKEN_EMPTY = 402;
    const REFRESH_TOKEN_EMPTY = 403;
    
    const EXTENSION_NOT_MATCH = 410; // 扩展没有匹配成功

    const FILE_SAVE_FAILED = 500;
    const RECORD_NOT_FOUND = 501; // 记录未找到
    const DELETE_FAILED = 502; // 删除失败
    const CREATE_FAILED = 503; // 添加记录失败 
    const UPDATE_FAILED = 504; // 添加记录失败
}
