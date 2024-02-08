<?php
/**
 * Permet de merger les données venant d'un tableau à un objet faq
 * @author Gourdon Aymeric
 * @version 1.0
 */

namespace App\Utils\Content\Faq;

use App\Entity\Admin\Content\Faq\Faq;

class FaqPopulate
{
    private const KEY_FAQ_TRANSLATION = 'faqTranslations';

    /**
     * @var Faq
     */
    private Faq $faq;

    /**
     * @var array
     */
    private array $populate;

    /**
     * Constructeur
     * @param Faq $faq
     * @param array $populate
     */
    public function __construct(Faq $faq, array $populate)
    {
        $this->populate = $populate;
        $this->faq = $faq;
    }

    /**
     * Merge les données de $populate dans $faq
     * @return $this
     */
    public function populate(): static
    {
        $this->populateFAQTranslation();
        return $this;
    }

    /**
     * Merge les données de $populate dans $faqTranslation
     * @return void
     */
    private function populateFAQTranslation(): void
    {
        if (isset($this->populate[self::KEY_FAQ_TRANSLATION])) {
            foreach ($this->faq->getFaqTranslations() as &$faqTranslation) {
                foreach ($this->populate[self::KEY_FAQ_TRANSLATION] as $dataTranslation) {
                    if ($faqTranslation->getLocale() === $dataTranslation['locale']) {
                        $faqTranslation = $this->mergeData($faqTranslation, $dataTranslation,
                            ['id', 'faq', 'locale']);
                    }
                }
            }
        }
    }

    /**
     * Retourne une FAQ
     * @return Faq
     */
    public function getFaq()
    {
        return $this->faq;
    }

    /**
     * Merge des données de $populate dans $object sans prendre en compte $exclude
     * @param mixed $object
     * @param array $populate
     * @param array $exclude
     * @return mixed
     */
    private function mergeData(mixed $object, array $populate, array $exclude = []): mixed
    {
        foreach ($populate as $key => $value) {
            if (in_array($key, $exclude)) {
                continue;
            }
            $func = 'set' . ucfirst($key);
            $object->$func($value);
        }
        return $object;
    }
}
