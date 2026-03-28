<?php

namespace AppBundle\Templating\Helper;

use Twig\Environment;

class SocialBarHelper
{
    protected $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function socialButtons($parameters)
    {
        return $this->twig->render('@App/helper/socialButtons.html.twig', $parameters);
    }

    public function facebookButton($parameters)
    {
        return $this->twig->render('@App/helper/facebookButton.html.twig', $parameters);
    }

    public function twitterButton($parameters)
    {
        return $this->twig->render('@App/helper/twitterButton.html.twig', $parameters);
    }

    public function googlePlusButton($parameters)
    {
        return $this->twig->render('@App/helper/googlePlusButton.html.twig', $parameters);
    }
}
