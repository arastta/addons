<?php
/**
 * @package     Arastta eCommerce
 * @copyright   2015-2017 Arastta Association. All rights reserved.
 * @copyright   See CREDITS.txt for credits and other copyright notices.
 * @license     GNU GPL version 3; see LICENSE.txt
 * @link        https://arastta.org
 */

class Divido_Util_Set implements IteratorAggregate
{
    private $_elts;

    public function __construct($members = array())
    {
        $this->_elts = array();
        foreach ($members as $item) {
            $this->_elts[$item] = true;
        }
    }

    public function includes($elt)
    {
        return isset($this->_elts[$elt]);
    }

    public function add($elt)
    {
        $this->_elts[$elt] = true;
    }

    public function discard($elt)
    {
        unset($this->_elts[$elt]);
    }

    public function toArray()
    {
        return array_keys($this->_elts);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->toArray());
    }
}
