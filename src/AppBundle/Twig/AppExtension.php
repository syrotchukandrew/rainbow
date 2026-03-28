<?php

namespace AppBundle\Twig;

use AppBundle\Templating\Helper\SocialBarHelper;
use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $locales;
    private $socialBarHelper;

    public function __construct($locales, SocialBarHelper $socialBarHelper)
    {
        $this->locales = $locales;
        $this->socialBarHelper = $socialBarHelper;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('dots3', array($this, 'dots3'), array('is_safe' => array('html'))),
            new TwigFunction('facebookButton', array($this, 'getFacebookLikeButton'), array('is_safe' => array('html'))),
            new TwigFunction('twitterButton', array($this, 'getTwitterButton'), array('is_safe' => array('html'))),
            new TwigFunction('googlePlusButton', array($this, 'getGooglePlusButton'), array('is_safe' => array('html'))),
            new TwigFunction('socialButtons', array($this, 'getSocialButtons'), array('is_safe' => array('html'))),
            new TwigFunction('locales', array($this, 'getLocales')),
        );
    }

    public function getSocialButtons($parameters = array())
    {
        if (!array_key_exists('facebook', $parameters)){
            $render_parameters['facebook'] = array();
        }else if(is_array($parameters['facebook'])){
            $render_parameters['facebook'] = $parameters['facebook'];
        }else{
            $render_parameters['facebook'] = false;
        }

        if (!array_key_exists('twitter', $parameters)){
            $render_parameters['twitter'] = array();
        }else if(is_array($parameters['twitter'])){
            $render_parameters['twitter'] = $parameters['twitter'];
        }else{
            $render_parameters['twitter'] = false;
        }

        if (!array_key_exists('googleplus', $parameters)){
            $render_parameters['googleplus'] = array();
        }else if(is_array($parameters['googleplus'])){
            $render_parameters['googleplus'] = $parameters['googleplus'];
        }else{
            $render_parameters['googleplus'] = false;
        }

        return $this->socialBarHelper->socialButtons($render_parameters);
    }

    public function getFacebookLikeButton($parameters = array())
    {
        $parameters = $parameters + array(
            'url' => 'http://anraduga.ck.ua',
            'locale' => 'en_US',
            'send' => false,
            'width' => 300,
            'showFaces' => false,
            'layout' => 'button_count',
        );

        return $this->socialBarHelper->facebookButton($parameters);
    }

    public function getTwitterButton($parameters = array())
    {
        $parameters = $parameters + array(
            'url' => 'http://anraduga.ck.ua',
            'locale' => 'en',
            'message' => 'I want to share that page with you',
            'text' => 'Tweet',
            'via' => 'The Acme team',
            'tag' => 'ttot',
        );

        return $this->socialBarHelper->twitterButton($parameters);
    }

    public function getGooglePlusButton($parameters = array())
    {
        $parameters = $parameters + array(
            'url' => 'http://anraduga.ck.ua',
            'locale' => 'en',
            'size' => 'medium',
            'annotation' => 'bubble',
            'width' => '300',
        );

        return $this->socialBarHelper->googlePlusButton($parameters);
    }

    public function getLocales()
    {
        $localeCodes = explode('|', $this->locales);

        $locales = array();
        foreach ($localeCodes as $localeCode) {
            $locales[] = array('code' => $localeCode, 'name' => Locales::getName($localeCode, $localeCode));
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
}
