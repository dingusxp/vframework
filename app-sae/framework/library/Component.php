<?php
/**
 * vframework 定义了组件的模式。一套组件包含以下内容：
 * 一个以组件名命名的主类：通常会有一个 factory 方法，通过参数 (engine, option) 来加载所需组件
 * 一个或多个（分布在不同类库下）以组件名命名的目录及放在里面的若干组件实例类；
 * 两个基础类：
 * [组件名]_Exception 定义异常代码
 * [组件名]_Abstract 定义组件通用方法
 *
 * 说明：组件实例类的名称可以使用 [目录]_[文件名] 的标准格式,也可以使用命名空间；但是两个基础类必须用标准格式
 */

/**
 * 组件
 */
class Component {

    /**
     * 工厂
     * @param <type> $componentName
     * @param <type> $engine
     * @param <type> $option
     */
    protected static function _factory($componentName, $engine, $option = array()) {

        // 兼容直接传入配置数组的方式
        if (is_array($engine)) {
            $option = isset($engine['option']) ? $engine['option'] : array();
            $engine = $engine['engine'];
        }

        // 检查参数
        $engine = ucfirst($engine);
        if ($engine == 'Abstract' || !preg_match('/^\w{1,64}$/', $engine)) {
            throw new Component_Exception(V::t('Bad component name: {name}', array('name' => $engine), 'framework'),
                Component_Exception::E_COMPONENT_NOT_EXIST);
        }

        // 获取实例
        $class = $componentName . '_' . $engine;
        $abstractClass = $componentName . '_Abstract';
        $exceptionClass = $componentName . '_Exception';
        try {
            if (class_exists($class) || (($class = str_replace('_', '\\', $class)) && class_exists($class))) {
                $object = new $class($option);
                if ($object instanceof $abstractClass) {
                    return $object;
                }
            }
        } catch (Exception $e) {
            throw new $exceptionClass(V::t('Component init error: {message}', array('message' => $e->getMessage()), 'framework'),
                Component_Exception::E_COMPONENT_INIT_FAILED);
        }

        throw new $exceptionClass(V::t('Component does not exist: {name}', array('name' => $class), 'framework'),
                Component_Exception::E_COMPONENT_NOT_EXIST);
    }
}