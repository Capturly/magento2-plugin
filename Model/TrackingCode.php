<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Capturly\Capturly\Model;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\Error;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 * @author Capturly
 */
class TrackingCode extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param StoreManagerInterface $storeManager
     * @param Config $eavConfig
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        StoreManagerInterface $storeManager,
        Config $eavConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validates the tracking code.
     *
     * @return $this
     * @throws Exception
     */
    public function validateBeforeSave()
    {
        if (!empty($this->getValue()) && preg_match("/t-.{24}['\"]\)/", $this->getValue()) !== 0) {
            preg_match("/t-.{24}/", $this->getValue(), $outPutArray);
            preg_match("/\'\d+\'/", $this->getValue(), $piwikSiteId);

            $accountId = $outPutArray[0];

            if (!empty($piwikSiteId)) {
                $piwikSiteId = str_replace("'","", $piwikSiteId[0]);
                $newTrackingCode = $this->getNewCapturlyTrackingCode($accountId, $piwikSiteId);
                $this->setValue($newTrackingCode);
            } else {
                $error = 'The given tracking code is not valid.';

                $exception = new Exception(new Phrase($error));
                $exception->addMessage(new Error($error));

                throw $exception;
            }
        } else {
            $error = 'The given tracking code is not valid.';

            $exception = new Exception(new Phrase($error));
            $exception->addMessage(new Error($error));

            throw $exception;
        }

        return parent::validateBeforeSave();
    }

    /**
     * Builds the newest tracking code.
     *
     * @param $accountId
     * @param $piwikId
     * @return string
     */
    private function getNewCapturlyTrackingCode($accountId, $piwikId)
    {
        return "<script>
        function trq(){(trq.q=trq.q||[]).push(arguments);}
        trq('account', '" . $accountId . "');
        var _paq=_paq||[];
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u='//capturly.com/';
            _paq.push(['setTrackerUrl', u+'capturly-track.php']);
            _paq.push(['setSiteId', '" . $piwikId . "']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'capturly-track-js.js';
            s.parentNode.insertBefore(g,s);
        })();
        </script>";
    }
}
