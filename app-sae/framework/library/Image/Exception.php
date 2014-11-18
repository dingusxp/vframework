<?php

/**
 * 图片出来异常
 */
class Image_Exception extends V_Exception {

    /**
     * 环境不支持
     */
    const E_IMAGE_RUMTIME_ERROR = 201;

    /**
     * 图片尚未初始化
     */
    const E_IMAGE_IM_NOT_INIT = 202;

    /**
     * 图片初始化失败
     */
    const E_IMAGE_INIT_ERROR = 203;

    /**
     * 图片处理失败
     */
    const E_IMAGE_OPERATION_ERROR = 204;

    /**
     * 图片处理参数有误
     */
    const E_IMAGE_PARAM_ERROR = 205;

    /**
     * 不支持该类型的图片
     */
    const E_IMAGE_TYPE_NOT_SUPPORT = 206;
}