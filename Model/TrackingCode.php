<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Capturly\Capturly\Model;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Message\Error;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\Exception;

/**
 *
 * @author Capturly
 */
class TrackingCode extends Value
{

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
            $accountId = $outPutArray[0];

            $this->setValue($this->generateTrackingCode($accountId));

        } else {
            $error = 'The given tracking code is not valid.';

            $exception = new Exception(new Phrase($error));
            $exception->addMessage(new Error($error));

            throw $exception;
        }

        return parent::validateBeforeSave(); // TODO: Change the autogenerated stub
    }

    /**
     * Builds capturly tracking code with the given account ID.
     *
     * @param $accountId
     * @return string
     */
    private function generateTrackingCode($accountId)
    {
        return '<script>
        trq("account", "' . $accountId . '");
        (function(d,t){
        var s=d.createElement(t),c=d.getElementsByTagName(t)[0];
        s.async=1;
        s.src="https://cdn.capturly.com/js/track.js";
        c.parentNode.insertBefore(s,c);
        }(document,\'script\'));function trq(){(trq.q=trq.q||[]).push(arguments);}</script>';
    }
}