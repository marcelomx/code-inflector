<?php

namespace ClickLab\Inflector;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class FileInflector extends BaseInflector
{
    const SAVE_MODE_PREVIEW   = 0;
    const SAVE_MODE_BACKUP    = 1;
    const SAVE_MODE_OVERWRITE = 2;

    protected $backupExtension  = '.backup~';
    protected $previewExtension = '.preview~';
    protected $file;
    protected $originalContent;
    protected $content;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->loadContent();
    }

    /**
     * @return string
     */
    public function loadContent()
    {
        $this->originalContent = $this->content = static::loadFile($this->file);
        return $this->content;
    }

    /**
     * @param array $values
     * @param int $mode
     * @return string
     */
    public function inflect(array $values = array(), $mode = self::MODE_CAMELIZE)
    {
        $this->content = static::inflectContent($this->content, $values, $mode);
        return $this->content;
    }

    /**
     * @param bool $original
     * @return string
     */
    public function getContent($original = false)
    {
        return $original ? $this->originalContent : $this->content;
    }

    /**
     * @return null
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return string
     */
    public static function loadFile($file)
    {
        $file = (string) $file;

        if (!file_exists($file) || !is_readable($file)) {
            throw new \RuntimeException(
                sprintf('Invalid or not readable file %s', $file)
            );
        }

        return file_get_contents($file);
    }

    /**
     * @param int $mode
     * @return void|null
     */
    public function save($mode = self::SAVE_MODE_PREVIEW)
    {
        if (self::SAVE_MODE_PREVIEW == $mode) {
            $previewFile = $this->file . $this->previewExtension;
            file_put_contents($previewFile, $this->content);
        }

        if (self::SAVE_MODE_BACKUP == $mode) {
            $backupFile = $this->file . $this->backupExtension;
            copy($this->file, $backupFile);
            file_put_contents($this->file, $this->content);
        }

        if (self::SAVE_MODE_OVERWRITE == $mode) {
            file_put_contents($this->file, $this->content);
        }
    }

    /**
     * @param bool $refreshContent
     * @return void|null
     */
    public function restore($refreshContent = true)
    {
        if ($backupFile = $this->hasBackup()) {
            copy($backupFile, $this->file);
            if ($refreshContent) $this->loadContent();
            @unlink($backupFile);
        }
    }

    /**
     * @return string|bool
     */
    public function hasBackup()
    {
        $backupFile = $this->file . $this->backupExtension;
        return file_exists($backupFile) ? $backupFile : false;
    }
}