<?php

class Fooman_SpeedsterAdvanced_Model_Observer
{

    public function httpResponseSendBefore(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfigFlag('dev/html/minify')) {
            /** @var Mage_Core_Controller_Response_Http $response */
            $response = $observer->getData('response');
            $html     = $response->getBody();
            // only minify HTML content!
            if (!empty($html) && $html[0] !== '{'
                && Mage::helper('speedsterAdvanced')->hasContentTypeHtmlHeader($response->getHeaders())) {
                $html = Mage::getModel('speedsterAdvanced/html', $html)->minify($html);
                $response->setBody($html);
            }
        }

        if (Mage::getStoreConfigFlag('dev/js/defer')) {
            /** @var Mage_Core_Controller_Response_Http $response */
            $response = $observer->getData('response');
            $html     = $response->getBody();

            if (stripos($html, "</body>") === false) return;
            preg_match_all('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', $html, $scripts);
            if ($scripts and isset($scripts[0]) and $scripts[0]) {
                $html = preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', $response->getBody());
                $scripts = implode("", $scripts[0]);
                $html = str_ireplace("</body>", "$scripts</body>", $html);
                $response->setBody($html);
            }
        }
    }

}
