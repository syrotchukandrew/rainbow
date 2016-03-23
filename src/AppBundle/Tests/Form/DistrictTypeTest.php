<?php
namespace AppBundle\Tests\Form;

use AppBundle\Entity\District;
use AppBundle\Form\DistrictType;
use Symfony\Component\Form\Test\TypeTestCase;

class DistrictTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'title' => 'Test title',
        );
        $form = $this->factory->create(DistrictType::class);
        $object = new District();
        $object->setTitle('Test title');
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($object, $form->getData());
        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}