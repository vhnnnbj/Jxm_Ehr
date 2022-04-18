<?php


namespace Jxm\Ehr\Model;


class Role extends EhrBasicModel
{
    /**
     * 系统固定角色
     */
    const Type_Sys_Const = 1;
    /**
     * 基本角色（系统角色）
     */
    const Type_Sys_Basic = 5;
    /**
     * 普通角色（系统角色）
     */
    const Type_Sys_Normal = 10;
    /**
     * 平台角色（集团职能）
     */
    const Type_Platform_All = 20;
    const Type_Platform_Normal = 25;
    /**
     * 体系角色
     */
    const Type_BG_Admin = 40;
    const Type_BG_Normal = 45;

    /**
     * 片区角色(待定)
     */
    const Type_Area_Admin = 60;     //总片区管理员
    const Type_Area_Normal = 65;    //总片区权限
    const Type_Area_Sub = 70;   //子片区权限

    /**
     * 公司角色
     */
    const Type_Company_Admin = 80;
    const Type_Company_Normal = 85;
    const Type_Department_Normal = 90;
}
