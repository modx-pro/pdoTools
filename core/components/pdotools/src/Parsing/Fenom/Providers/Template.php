<?php

namespace ModxPro\PdoTools\Parsing\Fenom\Providers;

use PDO;
use Iterator;
use Fenom\ProviderInterface;
use MODX\Revolution\modX;
use MODX\Revolution\modChunk;
use MODX\Revolution\modTemplate;
use ModxPro\PdoTools\CoreTools;

class Template implements ProviderInterface
{
    /** @var modX $modx */
    public $modx;
    /** @var CoreTools $pdoTools */
    public $pdoTools;


    public function __construct(modX $modx, CoreTools $pdoTools)
    {
        $this->modx = $modx;
        $this->pdoTools = $pdoTools;
    }


    /**
     * @param string $tpl
     *
     * @return bool
     */
    public function templateExists($tpl)
    {
        $c = is_numeric($tpl) && $tpl > 0
            ? $tpl
            : ['templatename' => $tpl];

        return (bool)$this->modx->getCount(modTemplate::class, $c);
    }


    /**
     * @param string $tpl
     * @param int $time
     *
     * @return string
     */
    public function getSource($tpl, &$time)
    {
        $content = '';
        if ($pos = strpos($tpl, '@')) {
            $propertySet = substr($tpl, $pos + 1);
            $tpl = substr($tpl, 0, $pos);
        }
        $c = is_numeric($tpl) && $tpl > 0
            ? $tpl
            : ['templatename' => $tpl];
        /** @var modChunk $chunk */
        if ($element = $this->modx->getObject(modTemplate::class, $c)) {
            $content = $element->getContent();

            $properties = [];
            if (!empty($propertySet)) {
                if ($tmp = $element->getPropertySet($propertySet)) {
                    $properties = $tmp;
                }
            } else {
                $properties = $element->getProperties();
            }
            if (!empty($content) && !empty($properties)) {
                $useFenom = $this->pdoTools->getConfig('useFenom');
                $this->pdoTools->config(['useFenom' => false]);

                $content = $this->pdoTools->parseChunk('@INLINE ' . $content, $properties);
                $this->pdoTools->config(['useFenom' => $useFenom]);
            }
        }

        return $content;
    }


    /**
     * @param string $tpl
     *
     * @return int
     */
    public function getLastModified($tpl)
    {
        $c = is_numeric($tpl) && $tpl > 0
            ? $tpl
            : ['templatename' => $tpl];
        /** @var modChunk $chunk */
        if ($chunk = $this->modx->getObject(modTemplate::class, $c)) {
            if ($chunk->isStatic() && $file = $chunk->getSourceFile()) {
                return filemtime($file);
            }
        }

        return time();
    }


    /**
     * Verify templates (check mtime)
     *
     * @param array $templates [template_name => modified, ...] By conversation, you may trust the template's name
     *
     * @return bool if true - all templates are valid else some templates are invalid
     */
    public function verify(array $templates)
    {
        return true;
    }


    /**
     * Get all names of template from provider
     * @return array|Iterator
     */
    public function getList()
    {
        $c = $this->modx->newQuery(modTemplate::class);
        $c->select('templatename');
        if ($c->prepare() && $c->stmt->execute()) {
            return $c->stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return [];
    }

}