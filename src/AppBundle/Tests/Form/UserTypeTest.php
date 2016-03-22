<?php
namespace AppBundle\Tests\Form;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'email' => 'test@user.com',
            'username' => 'username',
            'plainPassword' => 'plainPassword'
        );
        $form = $this->factory->create(UserType::class);
        $object = new User();
        $object->setEmail('test@user.com');
        $object->setUsername('username');
        $object->setPlainPassword('plainPassword');
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $view = $form->createView();
        $children = $view->children;
        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}