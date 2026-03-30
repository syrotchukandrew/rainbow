<?php

declare(strict_types=1);

namespace AppBundle\Twig;

use AppBundle\Templating\Helper\SocialBarHelper;
use Symfony\Component\Intl\Locales;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private string $locales;
    private SocialBarHelper $socialBarHelper;

    public function __construct(string $locales, SocialBarHelper $socialBarHelper)
    {
        $this->locales = $locales;
        $this->socialBarHelper = $socialBarHelper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('dots3', [$this, 'dots3'], ['is_safe' => ['html']]),
            new TwigFunction('facebookButton', [$this, 'getFacebookLikeButton'], ['is_safe' => ['html']]),
            new TwigFunction('twitterButton', [$this, 'getTwitterButton'], ['is_safe' => ['html']]),
            new TwigFunction('googlePlusButton', [$this, 'getGooglePlusButton'], ['is_safe' => ['html']]),
            new TwigFunction('socialButtons', [$this, 'getSocialButtons'], ['is_safe' => ['html']]),
            new TwigFunction('locales', [$this, 'getLocales']),
        ];
    }

    public function getSocialButtons(array $parameters = []): string
    {
        if (!array_key_exists('facebook', $parameters)) {
            $render_parameters['facebook'] = [];
        } elseif (is_array($parameters['facebook'])) {
            $render_parameters['facebook'] = $parameters['facebook'];
        } else {
            $render_parameters['facebook'] = false;
        }

        if (!array_key_exists('twitter', $parameters)) {
            $render_parameters['twitter'] = [];
        } elseif (is_array($parameters['twitter'])) {
            $render_parameters['twitter'] = $parameters['twitter'];
        } else {
            $render_parameters['twitter'] = false;
        }

        if (!array_key_exists('googleplus', $parameters)) {
            $render_parameters['googleplus'] = [];
        } elseif (is_array($parameters['googleplus'])) {
            $render_parameters['googleplus'] = $parameters['googleplus'];
        } else {
            $render_parameters['googleplus'] = false;
        }

        return $this->socialBarHelper->socialButtons($render_parameters);
    }

    public function getFacebookLikeButton(array $parameters = []): string
    {
        $parameters = $parameters + [
            'url' => 'http://anraduga.ck.ua',
            'locale' => 'en_US',
            'send' => false,
            'width' => 300,
            'showFaces' => false,
            'layout' => 'button_count',
        ];

        return $this->socialBarHelper->facebookButton($parameters);
    }

    public function getTwitterButton(array $parameters = []): string
    {
        $parameters = $parameters + [
            'url' => 'http://anraduga.ck.ua',
            'locale' => 'en',
            'message' => 'I want to share that page with you',
            'text' => 'Tweet',
            'via' => 'The Acme team',
            'tag' => 'ttot',
        ];

        return $this->socialBarHelper->twitterButton($parameters);
    }

    public function getGooglePlusButton(array $parameters = []): string
    {
        $parameters = $parameters + [
            'url' => 'http://anraduga.ck.ua',
            'locale' => 'en',
            'size' => 'medium',
            'annotation' => 'bubble',
            'width' => '300',
        ];

        return $this->socialBarHelper->googlePlusButton($parameters);
    }

    public function getLocales(): array
    {
        $localeCodes = explode('|', $this->locales);

        $locales = [];
        foreach ($localeCodes as $localeCode) {
            $locales[] = ['code' => $localeCode, 'name' => Locales::getName($localeCode, $localeCode)];
        }

        return $locales;
    }

    public function dots3(string $content, int $limit = 25): string
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
