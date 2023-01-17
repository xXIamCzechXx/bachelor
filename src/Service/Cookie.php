<?php

namespace App\Service;

use App\Form\CookieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Cookie extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Returns cookie form depending on cookies enabled
     * @author Dominik Mach
     */
    public function getCookieForm()
    {
        if (!$this->isConfirmed()) {
            if($form = $this->createForm(CookieFormType::class)){
                return $form->createView();
            }
        }

        return null;
    }

    /**
     * Returns true/false depending on user cookie agreement
     * @author Dominik Mach
     * @return bool
     */
    public function isConfirmed()
    {
        return isset($_COOKIE['cookie-agreement']) && $_COOKIE['cookie-agreement'] == 1;
    }

    /**
     * @return string
     */
    public function getGtagScript()
    {
        if ((isset($_COOKIE['cookie-agreement']))) {
            return self::renderGtagScript('update');
        } else if (isset($_COOKIE['cookie-confirmation'])) {
            return self::renderGtagScript('updated', 'denied', 'denied');
        } else {
            return self::renderGtagScript('default', 'denied', 'denied');
        }

    }

    /**
     * @param $type
     * @param $ad_storage
     * @param $analytics_storage
     * @return string
     */
    public static function renderGtagScript($type = 'default', $ad_storage = 'granted', $analytics_storage = 'granted')
    {
        $script = '';
        $script .= '
            <script type="text/javascript">
                function gtag(){
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push(arguments);
                }
                gtag("consent", "' . $type . '", {
                    "ad_storage": "' . $ad_storage . '",
                    "analytics_storage": "' . $analytics_storage . '",
                    "wait_for_update": 500
                  });
            </script>
        ';
        // This is not how it should work, but whatever
        if ($analytics_storage == 'granted') {
            $script .= '
                <script type="text/javascript">
                    window.dataLayer.push({"event":"cookie_consent_all"});
                </script>
            ';
        }
        return $script;
    }
}