<?php


namespace Jxm\Ehr\Model;


class UserInfo extends EhrBasicModel
{
    const State_Normal = 0;         //正常
    const State_Trail = 5;          //试用
    const State_Leave = 10;          //离职
    const State_Black = 20;          //黑名单
    const State_Exception = 30;      //异常
    const State_Unchecked = 40;      //未审核

}
