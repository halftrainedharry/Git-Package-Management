<?php
namespace GPM\Config\Parts\Fred;

use GPM\Config\Config;
use GPM\Config\FileParser;
use GPM\Config\Parts\Part;
use GPM\Config\Rules;

/**
 * Class Category
 *
 * @property-read string $name
 * @property-read string $description
 * @property-read bool $complete
 *
 * @property-read string $file
 * @property-read array | null $content
 * @property-read string $absoluteFilePath
 *
 * @package GPM\Config\Parts\Element
 */
class OptionSet extends Part
{
    protected $keyField = 'name';

    /** @var string */
    protected $name = '';

    /** @var string */
    protected $description = '';

    /** @var string */
    protected $file = '';

    /** @var string | array */
    protected $content = null;

    /** @var string */
    protected $absoluteFilePath = '';

    /** @var bool */
    protected $complete = true;

    /** @var int */
    protected $rank = 0;

    protected $rules = [
        'name' => [Rules::isString, Rules::notEmpty],
        'description' => [Rules::isString],
        'file' => [Rules::isString, Rules::notEmpty, Rules::elementFileExists],
        'complete' => [Rules::isBool],
    ];

    protected static $fileExtensions = [
        '.json',
        '.yaml',
        '.yml',
    ];

    protected function generator(): void
    {
        $baseElementsPath = $this->config->paths->core . 'elements' . DIRECTORY_SEPARATOR . 'fred' . DIRECTORY_SEPARATOR . 'optionsets' . DIRECTORY_SEPARATOR;

        if (!empty($this->name) && empty($this->file)) {
            $this->file = $this->name . '.json';
        }

        if (!empty($this->file) && file_exists($baseElementsPath . $this->file)) {
            $this->absoluteFilePath = $baseElementsPath . $this->file;
        } else {
            foreach (self::$fileExtensions as $fileExtension) {
                if (file_exists($baseElementsPath . $this->name . $fileExtension)) {
                    $this->file = $this->name . $fileExtension;
                    $this->absoluteFilePath = $baseElementsPath . $this->name . $fileExtension;
                    break;
                }
            }
        }
    }

    public function setConfig(Config $config): void
    {
        parent::setConfig($config);
    }

    public function getObject()
    {
        $obj = $this->config->modx->getObject('\\Fred\\Model\\FredElementOptionSet', ['name' => $this->name, 'theme' => $this->config->fred->getThemeId()]);

        if ($obj === null) {
            $obj = $this->config->modx->newObject('\\Fred\\Model\\FredElementOptionSet');
            $obj->set('name', $this->name);
            $obj->set('theme', $this->config->fred->getThemeId());
        }

        $obj->set('description', $this->description);
        $obj->set('complete', $this->complete);

        if ($this->content !== null) {
            $obj->set('content', $this->content);
        } else {
            $obj->set('content', FileParser::parseFile($this->absoluteFilePath));
        }

        return $obj;
    }

    public function deleteObject(): bool {
        $toDelete = $this->config->modx->getObject('\\Fred\\Model\\FredElementOptionSet', ['name' => $this->name, 'theme' => $this->config->fred->getThemeId()]);
        if ($toDelete) {
            return $toDelete->remove();
        }

        return false;
    }

    public function getBuildObject()
    {
        return $this->config->modx->getObject('\\Fred\\Model\\FredElementOptionSet', ['name' => $this->name, 'theme' => $this->config->fred->getThemeId()]);
    }
}
