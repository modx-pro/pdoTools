<?php

namespace ModxPro\PdoTools\Parsing;


use MODX\Revolution\modTag;
use xPDO\xPDO;

class Tag extends modTag
{
    /**
     * @param array|string $properties An array of properties or a formatted property string.
     * @param string       $content    Optional content to use for the element processing.
     *
     * @return bool
     */
    public function process($properties = null, $content = null)
    {
        $this->filterInput();

        if ($this->modx->getDebug() === true) {
            $this->modx->log(
                xPDO::LOG_LEVEL_DEBUG, "Processing Element: " . $this->get('name') .
                ($this->_tag ? "\nTag: {$this->_tag}" : "\n") .
                "\nProperties: " . print_r($this->_properties, true)
            );
        }
        if ($this->isCacheable() && isset($this->modx->elementCache[$this->_tag])) {
            $this->_output = $this->modx->elementCache[$this->_tag];
        } else {
            $this->_output = $this->_content;
            $this->filterOutput();
        }
        $this->_processed = true;

        return $this->_result;
    }


    /**
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

}
