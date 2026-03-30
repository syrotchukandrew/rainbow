<?php

declare(strict_types=1);

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
        return $this->twig->render('helper/socialButtons.html.twig', $parameters);
    }

    public function facebookButton($parameters)
    {
        return $this->twig->render('helper/facebookButton.html.twig', $parameters);
    }

    public function twitterButton($parameters)
    {
        return $this->twig->render('helper/twitterButton.html.twig', $parameters);
    }

    public function googlePlusButton($parameters)
    {
        return $this->twig->render('helper/googlePlusButton.html.twig', $parameters);
    }
}
