<?php

class modTemplateProvider implements \Fenom\ProviderInterface
{
    /** @var modX $modx */
    public $modx;
    /** @var pdoTools $pdoTools */
    public $pdoTools;


    function __construct(pdoTools $pdoTools)
    {
        $this->pdoTools = $pdoTools;
        $this->modx = $pdoTools->modx;
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
            : array('templatename' => $tpl);

        return (bool)$this->modx->getCount('modTemplate', $c);
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
            : array('templatename' => $tpl);
        /** @var modChunk $chunk */
        if ($element = $this->modx->getObject('modTemplate', $c)) {
            $content = $element->getContent();

            $properties = array();
            if (!empty($propertySet)) {
                if ($tmp = $element->getPropertySet($propertySet)) {
                    $properties = $tmp;
                }
            } else {
                $properties = $element->getProperties();
            }
            if (!empty($content) && !empty($properties)) {
                $useFenom = $this->pdoTools->config['useFenom'];
                $this->pdoTools->config['useFenom'] = false;

                $content = $this->pdoTools->parseChunk('@INLINE ' . $content, $properties);
                $this->pdoTools->config['useFenom'] = $useFenom;
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
            : array('templatename' => $tpl);
        /** @var modChunk $chunk */
        if ($chunk = $this->modx->getObject('modTemplate', $c)) {
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
     * @return array|\Iterator
     */
    public function getList()
    {
        $c = $this->modx->newQuery('modTemplate');
        $c->select('templatename');
        if ($c->prepare() && $c->stmt->execute()) {
            return $c->stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return array();
    }

}