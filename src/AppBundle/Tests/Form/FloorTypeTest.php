<?php
namespace AppBundle\Tests\Form;

use AppBundle\Entity\Estate;
use AppBundle\Form\FloorType;
use Symfony\Component\Form\Test\TypeTestCase;

class FloorTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'floor' => null, 'count_floor' => null
        );
        $form = $this->factory->create(FloorType::class);
        $object = new Estate();
        $object->setFloor(array('floor' => null, 'count_floor' => null));
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object->getFloor(), $form->getData());
        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}