<?php

/**
 * 分页 widget
 */
class HTML_Widget_Pager extends HTML_Widget_Abstract {

    /**
     * 构造函数
     * @param array $option
     */
    public function  __construct(array $option = array()) {

        // 默认值
        if (isset($option['max_page'])) {
            $option['max_page'] = intval($option['max_page']);
        } elseif (isset($option['total']) && isset($option['perpage'])) {
            $option['perpage'] = max(1, intval($option['perpage']));
            $option['max_page'] = ceil($option['total'] / $option['perpage']);
        } else {
            $option['max_page'] = 0;
        }

        // 当前页
        if (!isset($option['current'])) {
            $option['current'] = 1;
        }
        $option['current'] = max(1, min(intval($option['current']), $option['max_page']));

        // 显示多少个链接
        if (!isset($option['show_num'])) {
            $option['show_num'] = 11;
        }
        $option['show_num'] = max(5, intval($option['show_num']));

        // 链接 a 标签中 href 中的内容
        $option['is_ajax'] = !empty($option['is_ajax']) ? true : false;
        if (!$option['is_ajax']) {
            if (!isset($option['url'])) {
                $req = Web_Request::getInstance();
                $option['url'] = $req->url();
            }
            $option['url'] = Str::has($option['url'], '?')
                        ? preg_replace('/[\&]*page\=\d+/', '', $option['url']) . '&page={page}'
                        : $option['url'] . '?page={page}';
        }

        // 只有一页时仍然输出
        $option['output_single_page'] = !empty($option['output_single_page']) ? true : false;

        // 链接文本
        if (!isset($option['text'])) {
            $option['text'] = '{page}';
        }
        if (!isset($option['text_dot'])) {
            $option['text_dot'] = '...';
        }

        // 模板
        $option['template'] = !empty($option['template']) ? $option['template'] : 'widget/pager';

        parent::__construct($option);
    }

    /**
     * 获取链接数组
     */
    public function getLinks() {

        // 不需要分页
        if ($this->_option['max_page'] <= 0) {
            return array();
        }
        if ($this->_option['max_page'] == 1 && !$this->_option['output_single_page']) {
            return array();
        }

        // 计算每个链接
        $from = $to = $leftPad = $rightPad = 0;
        $half = intval($this->_option['show_num'] / 2);
        // 左起
        $from = $this->_option['current'] - $half;
        if ($from < 1) {
            $rightPad = 1 - $from;
            $from = 1;
        }
        // 右至
        $to = $this->_option['current'] + $half;
        if ($to > $this->_option['max_page']) {
            $leftPad = $to - $this->_option['max_page'];
            $to = $this->_option['max_page'];
        }
        // 补充
        if ($leftPad) {
            $from = max(1, $from - $leftPad);
        }
        if ($rightPad) {
            $to = min($this->_option['max_page'], $to + $rightPad);
        }

        $links = array();
        if ($from > 1) {
            // 第一页占用一个显示链接
            $from++;
            $links[] = array('type' => 'link', 'href' => $this->_link(1), 'text' => $this->_text(1), 'is_current' => false);
        }
        if ($from > 2) {
            $links[] = array('type' => 'dot', 'text' => $this->_option['text_dot']);
        }
        for ($i = $from; $i <= $to; $i++) {
            $isCurrent = $i == $this->_option['current'] ? true : false;
            $links[] = array('type' => 'link', 'href' => $this->_link($i), 'text' => $this->_text($i), 'is_current' => $isCurrent);
        }
        if ($to < $this->_option['max_page']) {
            // 最后一页占用一个显示链接
            array_pop($links);
            $to--;
        }
        if ($to < $this->_option['max_page'] - 1) {
            $links[] = array('type' => 'dot', 'text' => $this->_option['text_dot']);
        }
        if ($to < $this->_option['max_page']) {
            $links[] = array('type' => 'link', 'href' => $this->_link($this->_option['max_page']), 'text' => $this->_text($this->_option['max_page']), 'is_current' => false);
        }

        return $links;
    }

    /**
     * 获取指定页码的链接
     * @param <type> $page
     * @return <type>
     */
    public function _link($page) {

        return str_replace('{page}', $page, $this->_option['url']);
    }

    /**
     * 获取指定页码的文字
     */
    public function _text($page) {

        return str_replace('{page}', $page, $this->_option['text']);
    }

    /**
     * 输出 HTML
     * @return <type>
     */
    public function getHTML() {

        $links = $this->getLinks();
        if (!$links) {
            return '';
        }

        $view = View::factory(V::config('view'));
        $view->assign('links', $links);
        return $view->render($this->_option['template'], View::RENDER_NONE);
    }
}