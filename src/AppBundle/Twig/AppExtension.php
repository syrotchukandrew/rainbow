<?php
/**
 * Created by PhpStorm.
 * User: acer
 * Date: 28/01/16
 * Time: 16:14
 */

namespace AppBundle\Twig;

use Symfony\Component\Intl\Intl;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AppExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    private $locales;
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct($locales, $container)
    {
        $this->locales = $locales;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('dots3', array($this, 'dots3'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('facebookButton', array($this, 'getFacebookLikeButton'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('locales', array($this, 'getLocales')),

        );
    }

    // https://developers.facebook.com/docs/reference/plugins/like/
    public function getFacebookLikeButton($parameters = array())
    {
        // default values, you can override the values by setting them
        $parameters = $parameters + array(
                'url' => 'http://symfony.com',
                'locale' => 'en_US',
                'send' => false,
                'width' => 300,
                'showFaces' => false,
                'layout' => 'button_count',
            );

        return $this->container->get('app.socialBarHelper')->facebookButton($parameters);
    }

    public function getLocales()
    {
        $localeCodes = explode('|', $this->locales);

        $locales = array();
        foreach ($localeCodes as $localeCode) {
            $locales[] = array('code' => $localeCode, 'name' => Intl::getLocaleBundle()->getLocaleName($localeCode, $localeCode));
        }

        return $locales;
    }


    public function dots3($content, $limit = 25)
    {
        $words = explode(' ', (trim($content)));
        $countWords = count($words);
        if ($countWords < $limit) {
            $lim = $countWords;
        } else {
            $lim = $limit;
        }
        $words[($lim-1)] .= '...<em>Read More</em>...';
        $strResult = '';
        for ($i = 0; $i < $lim; $i++) {
            $strResult .= $words[$i].' ';
        }

        return $strResult;
    }


    public function getName()
    {
        return 'app_extension';
    }
}