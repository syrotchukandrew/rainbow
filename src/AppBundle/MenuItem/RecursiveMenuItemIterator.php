<?php
/**
 * Created by PhpStorm.
 * User: kate
 * Date: 17.02.16
 * Time: 16:31
 */

namespace AppBundle\MenuItem;


use AppBundle\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;


class RecursiveMenuItemIterator implements \RecursiveIterator
{

    /**
     * @var ArrayCollection
     */
    private $_data;

    public function __construct(ArrayCollection $data)
    {
        // initialize the iterator with the root menu, i.e. parent id null
        $this->_data = $data;
    }

    public function current()
    {
        return $this->_data->current();
    }

    public function next()
    {
        $this->_data->next();
    }

    public function key()
    {
        return $this->_data->key();
    }

    public function valid()
    {
        return $this->_data->current() instanceof Category;
    }

    public function rewind()
    {
        $this->_data->first();
    }

    public function hasChildren()
    {
        return ( ! $this->_data->current()->getChildren()->isEmpty());
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArrayChildren()
    {
       return $this->_data->current()->getChildren();

    }


    public function getChildren()
    {
        return new RecursiveMenuItemIterator($this->getArrayChildren());
    }

}