<?php

namespace ModxPro\PdoTools\Parsing\Fenom\Support;

use MODX\Revolution\modX;
use MODX\Revolution\modLexicon;

class Lexicon
{
    /** @var modX $modx */
    protected $modx;
    /** @var modLexicon $lexicon */
    protected $lexicon;


    /**
     * @param modX $modx
     */
    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->lexicon = $this->modx->services->get('lexicon');
    }


    /**
     *
     */
    public function load()
    {
        $topics = func_get_args();

        foreach ($topics as $topic) {
            $this->lexicon->load($topic);
        }
    }

}
