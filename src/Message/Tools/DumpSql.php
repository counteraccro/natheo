<?php
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Message qui permet de stocker les options pour le dump SQL
 */
namespace App\Message\Tools;

class DumpSql
{
    public function __construct(
        private array $options,
    )
    {}

    public function getOption(): array
    {
        return $this->options;
    }

}
