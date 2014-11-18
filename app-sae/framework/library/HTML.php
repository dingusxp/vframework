<?php
/**
 * HTML 辅助类
 */
class HTML {

    /**
     * 去除所有 html 标签内容
     * @param <type> $html
     */
    public static function clean($html, $allowTags = '') {

        return strip_tags($html, $allowTags);
    }

    /**
     * HTML 转义
     * @param <type> $html
     * @param <type> $convertAllEntities
     * @return <type>
     */
    public static function escape($html, $convertAllEntities = false) {

        if ($convertAllEntities) {
            return htmlentities($html, ENT_COMPAT, 'UTF-8', false);
        } else {
            return htmlspecialchars($html, ENT_COMPAT, 'UTF-8', false);
        }
    }

    /**
     * 输出 wedget
     * @param <type> $engine
     * @param <type> $option
     */
    public static function widget($engine, $option = array()) {

        $widget = HTML_Widget::factory($engine, $option);
        return $widget->getHTML();
    }
}