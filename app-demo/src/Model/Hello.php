<?php
/**
 * Model Hello
 */

class Model_Hello extends Model_Abstract {

    /**
     * hello world
     * @return string
     */
    public static function getWords() {

        return 'hell, world';
    }
}