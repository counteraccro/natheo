<?php
/**
 * Code associé aux options de l'application
 * @author Gourdon Aymeric
 * @version 1.0
 * @package App\Service\Admin\GlobalFunction
 */
namespace App\Service\Admin\System;

use App\Entity\Admin\Option;
use App\Service\AppService;
use phpDocumentor\Reflection\Types\Self_;

class OptionService extends AppService
{

    const GO_ADM_GLOBAL_ELEMENT_PAR_PAGE = 'GO_ADM_GLOBAL_ELEMENT_PAR_PAGE';
    const GO_ADM_GLOBAL_ELEMENT_PAR_PAGE_DEFAULT_VALUE = 20;
    const GO_ADM_THEME_ADMIN = 'GO_ADM_THEME_ADMIN';
    const GO_ADM_THEME_ADMIN_DEFAULT_VALUE = 'purple';
    const GO_ADM_GLOBAL_LANGUE = 'GO_ADM_GLOBAL_LANGUE';
    const GO_ADM_GLOBAL_LANGUE_DEFAULT_VALUE = 'fr';
    const GO_ADM_SHORT_DATE_FORMAT = 'GO_DATE_FORMAT_SHORT_DATE_FORMAT';
    const GO_ADM_SHORT_DATE_FORMAT_DEFAULT_VALUE = '%d/%m/%G';
    const GO_ADM_TIME_FORMAT = 'GO_DATE_FORMAT_TIME_FORMAT';
    const GO_ADM_TIME_FORMAT_DEFAULT_VALUE = 'H:i:s';
    const GO_ADM_DATE_FORMAT = 'GO_DATE_FORMAT_DATE_FORMAT';
    const GO_ADM_DATE_FORMAT_DEFAULT_VALUE = '%A %e %B %Y';
    const GO_ADM_REPLACE_USER = 'GO_ADM_REPLACE_USER';
    const GO_ADM_REPLACE_USER_DEFAULT_VALUE = '1';
    const GO_ADM_REPLACE_USER_YES_VALUE = '1';
    const GO_ADM_CONFIRM_LEAVE = 'GO_ADM_CONFIRM_LEAVE';
    const GO_ADM_CONFIRM_LEAVE_DEFAULT_VALUE = '1';
    const GO_ADM_CONFIRM_LEAVE_YES_VALUE = '1';


    const KEY_SESSION_THEME_ADMIN = 'cms.global.theme_admin';

    /**
     * Permet de récupérer une option en fonction de sa clé. <br />
     * Si $onlyValue est à true, renvoi uniquement la valeur de l'option, sinon renvoi l'objet. <br />
     * Si l'option n'existe pas, elle est créée de façon automatique en fonction de la clé
     * @param string $key
     * @param string $default_value
     * @param bool $onlyValue
     * @return string|Option
     */
    public function getOptionByKey(string $key, string $default_value = '', bool $onlyValue = false): string|Option
    {
        $option = $this->getByKey($key);

        if($option == null)
        {
            $option = new Option();
            $option->setName($key);
            $option->setValue($default_value);
            $this->doctrine->getManager()->persist($option);
            $this->doctrine->getManager()->flush();
        }

        if($onlyValue)
        {
            return $option->getValue();
        }
        return $option;
    }

    /**
     * Permet de mettre à jour une option
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function UpdateByKey(string $key, string $value): bool
    {
        $option = $this->getByKey($key);

        // Cas l'option n'existe pas
        if($option == null)
        {
            return false;
        }

        $option->setValue($value);
        $this->doctrine->getManager()->persist($option);
        $this->doctrine->getManager()->flush();
        return true;
    }

    /**
     * Renvoi une option en fonction de sa clé
     * @param string $key
     * @return Option|null
     */
    private function getByKey(string $key): ?Option
    {
        $optionRepo = $this->doctrine->getRepository(Option::class);
        return $optionRepo->findOneBy(['name' => $key]);
    }

    /**
     * Permet de récupérer l'option GO_ADM_GLOBAL_ELEMENT_PAR_PAGE
     * @param bool $onlyValue True uniquement valeur sinon objet option
     * @return Option|string
     */
    public function getOptionElementParPage(bool $onlyValue = true): string|Option
    {
        return $this->getOptionByKey(self::GO_ADM_GLOBAL_ELEMENT_PAR_PAGE, self::GO_ADM_GLOBAL_ELEMENT_PAR_PAGE_DEFAULT_VALUE, $onlyValue);
    }

    /**
     * Permet de récupérer l'option GO_DATE_FORMAT_DATE_FORMAT
     * @param bool $onlyValue
     * @return Option|string
     */
    public function getOptionFormatDate(bool $onlyValue = true): string|Option
    {
        return $this->getOptionByKey(self::GO_ADM_DATE_FORMAT, self::GO_ADM_DATE_FORMAT_DEFAULT_VALUE, $onlyValue);
    }

    /**
     * Permet de récupérer l'option GO_DATE_FORMAT_SHORT_DATE_FORMAT
     * @param bool $onlyValue
     * @return Option|string
     */
    public function getOptionShortFormatDate(bool $onlyValue = true): string|Option
    {
        return $this->getOptionByKey(self::GO_ADM_SHORT_DATE_FORMAT, self::GO_ADM_SHORT_DATE_FORMAT_DEFAULT_VALUE, $onlyValue);
    }

    /**
     * Permet de récupérer l'option GO_ADM_DATE_FORMAT_TIME_FORMAT
     * @param bool $onlyValue
     * @return Option|string
     */
    public function getOptionTimeFormat(bool $onlyValue = true): string|Option
    {
        return $this->getOptionByKey(self::GO_ADM_TIME_FORMAT, self::GO_ADM_TIME_FORMAT_DEFAULT_VALUE, $onlyValue);
    }

    /**
     * Permet de récupérer l'option GO_ADM_REPLACE_USER
     * @param bool $onlyValue
     * @return Option|string
     */
    public function getOptionReplaceUser(bool $onlyValue = true): string|Option
    {
        return $this->getOptionByKey(self::GO_ADM_REPLACE_USER, self::GO_ADM_REPLACE_USER_DEFAULT_VALUE, $onlyValue);
    }

    /**
     * Permet de récupérer l'option GO_ADM_CONFIRM_LEAVE
     * @param bool $onlyValue
     * @return Option|string
     */
    public function getOptionConfirmLeave(bool $onlyValue = true): string|Option
    {
        return $this->getOptionByKey(self::GO_ADM_CONFIRM_LEAVE, self::GO_ADM_CONFIRM_LEAVE_DEFAULT_VALUE, $onlyValue);
    }
}