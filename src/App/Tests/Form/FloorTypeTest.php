<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Estate;
use App\Form\FloorType;
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