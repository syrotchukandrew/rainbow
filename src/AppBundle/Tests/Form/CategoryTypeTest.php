<?php
namespace AppBundle\Tests\Form;

use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

class CategoryTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'title' => 'Test title',
        );
        $form = $this->factory->create(CategoryType::class);
        $object = new Category();
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