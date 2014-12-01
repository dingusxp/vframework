<?php
/**
 * controller index
 */

class Controller_Index extends Controller_Base {

    /**
     * action index
     * 首页
     * @return type
     */
    public function actionIndex() {

        Timer::getInstance()->log('action in~~');

        $tplData = array(
            'title' => 'vframework app-demo - index',
        );
        return $this->_view->renderLayout('index/index', 'layout', $tplData, View::LAYOUT_REBUILD_RESOURCE);
    }
}