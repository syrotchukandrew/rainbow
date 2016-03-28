<?php
namespace AppBundle\Tests\Form;

use AppBundle\Entity\MenuItem;
use AppBundle\Form\MenuItemType;
use Symfony\Component\Form\Test\TypeTestCase;

class MenuItemTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'title' => 'Test title',
            'description' => 'Test description'
        );
        $form = $this->factory->create(MenuItemType::class);
        $object = new MenuItem();
        $object->setTitle('Test title');
        $object->setDescription('Test description');
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